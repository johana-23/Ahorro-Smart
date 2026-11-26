package com.smartahorro.smart_ahorro_api.security;

import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.List;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertThrows;
import static org.junit.jupiter.api.Assertions.assertTrue;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.test.context.bean.override.mockito.MockitoBean;
import com.smartahorro.smart_ahorro_api.service.TokenRevocationService;
import static org.mockito.Mockito.when;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.security.oauth2.jose.jws.SignatureAlgorithm;
import org.springframework.security.oauth2.jwt.JwsHeader;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.security.oauth2.jwt.JwtClaimsSet;
import org.springframework.security.oauth2.jwt.JwtDecoder;
import org.springframework.security.oauth2.jwt.JwtEncoder;
import org.springframework.security.oauth2.jwt.JwtEncoderParameters;
import org.springframework.security.oauth2.jwt.JwtException;

@SpringBootTest
class JwtConfigIntegrationTests {

    @Autowired
    private JwtEncoder jwtEncoder;

    @Autowired
    private JwtDecoder jwtDecoder;

    @MockitoBean
    private TokenRevocationService revocations;

    @Test
    void debeFirmarYValidarJwtRS256() {

        Instant now = Instant.now();
        when(revocations.isActive("jti-test", 1L)).thenReturn(true);

        JwtClaimsSet claims = JwtClaimsSet.builder()
                .issuer("https://auth.smart-ahorro.local")
                .subject("1")
                .audience(java.util.List.of("smart-ahorro-api"))
                .issuedAt(now)
                .notBefore(now)
                .id("jti-test")
                .expiresAt(now.plus(15, ChronoUnit.MINUTES))
                .build();

        JwsHeader header = JwsHeader
                .with(SignatureAlgorithm.RS256)
                .build();

        String token = jwtEncoder.encode(
                JwtEncoderParameters.from(header, claims)
        ).getTokenValue();

        Jwt decoded = jwtDecoder.decode(token);

        assertEquals("RS256", decoded.getHeaders().get("alg"));
        assertEquals("1", decoded.getSubject());
        assertEquals("https://auth.smart-ahorro.local", decoded.getIssuer().toString());
        assertTrue(decoded.getAudience().contains("smart-ahorro-api"));
        assertNotNull(decoded.getExpiresAt());
    }

    @Test
    void debeRechazarTokenManipulado() {

        Instant now = Instant.now();

        JwtClaimsSet claims = JwtClaimsSet.builder()
                .subject("1")
                .issuedAt(now)
                .expiresAt(now.plusSeconds(900))
                .build();

        JwsHeader header = JwsHeader
                .with(SignatureAlgorithm.RS256)
                .build();

        String token = jwtEncoder.encode(
                JwtEncoderParameters.from(header, claims)
        ).getTokenValue();

        String[] parts = token.split("\\.");

        // Cambiamos la firma para invalidarla.
        char first = parts[2].charAt(0);
        parts[2] = (first == 'A' ? "B" : "A")
                + parts[2].substring(1);

        String manipulatedToken = String.join(".", parts);

        assertThrows(
                JwtException.class,
                () -> jwtDecoder.decode(manipulatedToken)
        );

        

    }

    
        @Test
        void debeRechazarEmisorIncorrecto() {

            String token = crearToken(
                    "https://emisor-no-autorizado.local",
                    List.of("smart-ahorro-api"),
                    "1",
                    Instant.now().plusSeconds(900)
            );

            assertThrows(
                    JwtException.class,
                    () -> jwtDecoder.decode(token)
            );
        }

        @Test
        void debeRechazarAudienciaIncorrecta() {

            String token = crearToken(
                    "https://auth.smart-ahorro.local",
                    List.of("otra-api"),
                    "1",
                    Instant.now().plusSeconds(900)
            );

            assertThrows(
                    JwtException.class,
                    () -> jwtDecoder.decode(token)
            );
        }

        @Test
        void debeRechazarTokenExpirado() {

            String token = crearToken(
                    "https://auth.smart-ahorro.local",
                    List.of("smart-ahorro-api"),
                    "1",
                    Instant.now().minusSeconds(3600)
            );

            assertThrows(
                    JwtException.class,
                    () -> jwtDecoder.decode(token)
            );
        }

        @Test
        void debeRechazarIdentificadorInvalido() {

            String token = crearToken(
                    "https://auth.smart-ahorro.local",
                    List.of("smart-ahorro-api"),
                    "usuario-invalido",
                    Instant.now().plusSeconds(900)
            );

            assertThrows(
                    JwtException.class,
                    () -> jwtDecoder.decode(token)
            );
        }

        private String crearToken(
                String issuer,
                List<String> audience,
                String subject,
                Instant expiration
        ) {

            Instant now = Instant.now();
            when(revocations.isActive("jti-test", 1L)).thenReturn(true);
            Instant issuedAt = expiration.minusSeconds(900);
            if (issuedAt.isAfter(now)) issuedAt = now;

            JwtClaimsSet claims = JwtClaimsSet.builder()
                    .issuer(issuer)
                    .subject(subject)
                    .audience(audience)
                    .issuedAt(issuedAt)
                    .notBefore(issuedAt)
                    .id("jti-test")
                    .expiresAt(expiration)
                    .build();

            JwsHeader header = JwsHeader
                    .with(SignatureAlgorithm.RS256)
                    .build();

            return jwtEncoder.encode(
                    JwtEncoderParameters.from(header, claims)
            ).getTokenValue();
        }

}