package com.smartahorro.smart_ahorro_api.security;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.security.KeyFactory;
import java.security.interfaces.RSAPrivateKey;
import java.security.interfaces.RSAPublicKey;
import java.security.spec.PKCS8EncodedKeySpec;
import java.security.spec.X509EncodedKeySpec;
import java.util.Base64;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.oauth2.core.DelegatingOAuth2TokenValidator;
import org.springframework.security.oauth2.core.OAuth2Error;
import org.springframework.security.oauth2.core.OAuth2TokenValidator;
import org.springframework.security.oauth2.core.OAuth2TokenValidatorResult;
import org.springframework.security.oauth2.jose.jws.SignatureAlgorithm;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.security.oauth2.jwt.JwtDecoder;
import org.springframework.security.oauth2.jwt.JwtEncoder;
import org.springframework.security.oauth2.jwt.JwtValidators;
import org.springframework.security.oauth2.jwt.NimbusJwtDecoder;
import org.springframework.security.oauth2.jwt.NimbusJwtEncoder;

import com.smartahorro.smart_ahorro_api.service.TokenRevocationService;

import com.nimbusds.jose.jwk.JWKSet;
import com.nimbusds.jose.jwk.RSAKey;
import com.nimbusds.jose.jwk.source.ImmutableJWKSet;

@Configuration
public class JwtConfig {

    @Bean
    public JwtEncoder jwtEncoder(
            @Value("${smart-ahorro.jwt.private-key}") String privateKeyPath,
            @Value("${smart-ahorro.jwt.public-key}") String publicKeyPath
    ) throws Exception {

        RSAPrivateKey privateKey = loadPrivateKey(privateKeyPath);
        RSAPublicKey publicKey = loadPublicKey(publicKeyPath);

        RSAKey rsaKey = new RSAKey.Builder(publicKey)
                .privateKey(privateKey)
                .build();

        return new NimbusJwtEncoder(
                new ImmutableJWKSet<>(new JWKSet(rsaKey))
        );
    }

   
@Bean
public JwtDecoder jwtDecoder(
        @Value("${smart-ahorro.jwt.public-key}") String publicKeyPath,
        @Value("${smart-ahorro.jwt.issuer}") String issuer,
        @Value("${smart-ahorro.jwt.audience}") String audience,
        TokenRevocationService revocationService
) throws Exception {

    RSAPublicKey publicKey = loadPublicKey(publicKeyPath);

    NimbusJwtDecoder decoder = NimbusJwtDecoder
            .withPublicKey(publicKey)
            .signatureAlgorithm(SignatureAlgorithm.RS256)
            .build();

    OAuth2TokenValidator<Jwt> defaultValidator =
            JwtValidators.createDefaultWithIssuer(issuer);

    OAuth2TokenValidator<Jwt> audienceValidator = jwt -> {

        if (jwt.getAudience().contains(audience)) {
            return OAuth2TokenValidatorResult.success();
        }

        return OAuth2TokenValidatorResult.failure(
                new OAuth2Error(
                        "invalid_token",
                        "Audiencia JWT no autorizada",
                        null
                )
        );
    };

    OAuth2TokenValidator<Jwt> subjectValidator = jwt -> {

        String subject = jwt.getSubject();

        if (subject != null && subject.matches("[1-9][0-9]*")) {
            try {
                Long.parseLong(subject);
                return OAuth2TokenValidatorResult.success();
            } catch (NumberFormatException ignored) {
                // El identificador no cabe en Long.
            }
        }

        return OAuth2TokenValidatorResult.failure(
                new OAuth2Error(
                        "invalid_token",
                        "Identificador JWT invalido",
                        null
                )
        );
    };

    OAuth2TokenValidator<Jwt> expirationRequiredValidator = jwt -> {

        if (jwt.getExpiresAt() != null) {
            return OAuth2TokenValidatorResult.success();
        }

        return OAuth2TokenValidatorResult.failure(
                new OAuth2Error(
                        "invalid_token",
                        "El JWT debe incluir expiracion",
                        null
                )
        );
    };

    OAuth2TokenValidator<Jwt> lifetimeValidator = jwt -> {
        java.time.Instant issuedAt = jwt.getIssuedAt();
        java.time.Instant expiresAt = jwt.getExpiresAt();
        if (issuedAt != null && expiresAt != null
                && expiresAt.isAfter(issuedAt)
                && !expiresAt.isAfter(issuedAt.plusSeconds(900))) {
            return OAuth2TokenValidatorResult.success();
        }
        return OAuth2TokenValidatorResult.failure(
                new OAuth2Error("invalid_token", "Periodo de vigencia JWT invalido", null));
    };

    OAuth2TokenValidator<Jwt> revocationValidator = jwt -> {
        if (revocationService.isRevoked(jwt.getId())) {
            return OAuth2TokenValidatorResult.failure(
                    new OAuth2Error("invalid_token", "Token revocado", null));
        }
        return OAuth2TokenValidatorResult.success();
    };

    decoder.setJwtValidator(
            new DelegatingOAuth2TokenValidator<>(
                    defaultValidator,
                    audienceValidator,
                    subjectValidator,
                    expirationRequiredValidator,
                    lifetimeValidator,
                    revocationValidator
            )
    );

    return decoder;
}


    private RSAPrivateKey loadPrivateKey(String path) throws Exception {

        String pem = readPem(path)
                .replace("-----BEGIN PRIVATE KEY-----", "")
                .replace("-----END PRIVATE KEY-----", "")
                .replaceAll("\\s", "");

        byte[] decoded = Base64.getDecoder().decode(pem);

        PKCS8EncodedKeySpec keySpec =
                new PKCS8EncodedKeySpec(decoded);

        return (RSAPrivateKey) KeyFactory
                .getInstance("RSA")
                .generatePrivate(keySpec);
    }

    private RSAPublicKey loadPublicKey(String path) throws Exception {

        String pem = readPem(path)
                .replace("-----BEGIN PUBLIC KEY-----", "")
                .replace("-----END PUBLIC KEY-----", "")
                .replaceAll("\\s", "");

        byte[] decoded = Base64.getDecoder().decode(pem);

        X509EncodedKeySpec keySpec =
                new X509EncodedKeySpec(decoded);

        return (RSAPublicKey) KeyFactory
                .getInstance("RSA")
                .generatePublic(keySpec);
    }

    private String readPem(String path) throws IOException {
        return Files.readString(
                Path.of(path),
                StandardCharsets.UTF_8
        );
    }
}