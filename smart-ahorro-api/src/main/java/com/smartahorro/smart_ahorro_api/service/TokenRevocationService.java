package com.smartahorro.smart_ahorro_api.service;

import java.time.Instant;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

import org.springframework.stereotype.Service;

/**
 * Lista de tokens revocados (por jti) hasta su expiracion natural.
 * Se almacena en memoria: se pierde al reiniciar y no se comparte entre instancias.
 * Como el access token dura 15 minutos, el riesgo se limita a esa ventana.
 */
@Service
public class TokenRevocationService {

    private final Map<String, Instant> revoked = new ConcurrentHashMap<>();

    public void revoke(String jti, Instant expiresAt) {
        if (jti == null || jti.isBlank() || expiresAt == null) {
            return;
        }
        purgeExpired();
        revoked.put(jti, expiresAt);
    }

    public boolean isRevoked(String jti) {
        if (jti == null) {
            return false;
        }
        Instant expiresAt = revoked.get(jti);
        if (expiresAt == null) {
            return false;
        }
        if (!expiresAt.isAfter(Instant.now())) {
            revoked.remove(jti);
            return false;
        }
        return true;
    }

    private void purgeExpired() {
        Instant now = Instant.now();
        revoked.values().removeIf(expiresAt -> !expiresAt.isAfter(now));
    }
}
