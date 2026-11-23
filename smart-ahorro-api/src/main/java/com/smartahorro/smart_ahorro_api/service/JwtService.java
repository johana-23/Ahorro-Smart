package com.smartahorro.smart_ahorro_api.service;

import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.UUID;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.security.oauth2.jose.jws.SignatureAlgorithm;
import org.springframework.security.oauth2.jwt.JwsHeader;
import org.springframework.security.oauth2.jwt.JwtClaimsSet;
import org.springframework.security.oauth2.jwt.JwtEncoder;
import org.springframework.security.oauth2.jwt.JwtEncoderParameters;
import org.springframework.stereotype.Service;

import com.smartahorro.smart_ahorro_api.dto.LoginResponse;

@Service
public class JwtService {
    private final JwtEncoder encoder;
    private final String issuer;
    private final String audience;
    private final long lifetimeMinutes;

    public JwtService(JwtEncoder encoder,
            @Value("${smart-ahorro.jwt.issuer}") String issuer,
            @Value("${smart-ahorro.jwt.audience}") String audience,
            @Value("${smart-ahorro.jwt.access-token-minutes}") long lifetimeMinutes) {
        if (lifetimeMinutes != 15) {
            throw new IllegalArgumentException("La vigencia del access token debe ser 15 minutos");
        }
        this.encoder = encoder;
        this.issuer = issuer;
        this.audience = audience;
        this.lifetimeMinutes = lifetimeMinutes;
    }

    public LoginResponse issueAccessToken(Long userId) {
        if (userId == null || userId <= 0) {
            throw new IllegalArgumentException("Identificador de usuario invalido");
        }
        Instant now = Instant.now();
        JwtClaimsSet claims = JwtClaimsSet.builder()
                .issuer(issuer)
                .subject(userId.toString())
                .audience(List.of(audience))
                .issuedAt(now)
                .notBefore(now)
                .expiresAt(now.plus(lifetimeMinutes, ChronoUnit.MINUTES))
                .id(UUID.randomUUID().toString())
                .build();
        JwsHeader header = JwsHeader.with(SignatureAlgorithm.RS256).build();
        String token = encoder.encode(JwtEncoderParameters.from(header, claims)).getTokenValue();
        return new LoginResponse(token, "Bearer", lifetimeMinutes * 60);
    }
}
