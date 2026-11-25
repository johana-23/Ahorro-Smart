package com.smartahorro.smart_ahorro_api.security;

import java.time.Instant;

import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;
import org.junit.jupiter.api.Test;

import com.smartahorro.smart_ahorro_api.service.TokenRevocationService;

class TokenRevocationServiceTests {

    private final TokenRevocationService service = new TokenRevocationService();

    @Test
    void tokenRevocadoVigenteDebeSerRechazado() {
        service.revoke("jti-1", Instant.now().plusSeconds(600));
        assertTrue(service.isRevoked("jti-1"));
    }

    @Test
    void tokenNoRevocadoDebeSerAceptado() {
        assertFalse(service.isRevoked("otro"));
        assertFalse(service.isRevoked(null));
    }

    @Test
    void revocacionExpiradaSeDescarta() {
        service.revoke("jti-2", Instant.now().minusSeconds(5));
        assertFalse(service.isRevoked("jti-2"));
    }

    @Test
    void ignoraDatosIncompletos() {
        service.revoke(null, Instant.now().plusSeconds(60));
        service.revoke("jti-3", null);
        assertFalse(service.isRevoked("jti-3"));
    }
}
