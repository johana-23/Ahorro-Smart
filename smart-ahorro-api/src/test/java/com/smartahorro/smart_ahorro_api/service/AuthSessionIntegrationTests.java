package com.smartahorro.smart_ahorro_api.service;

import com.smartahorro.smart_ahorro_api.dto.RegisterRequest;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.security.authentication.BadCredentialsException;
import org.springframework.security.oauth2.jwt.JwtDecoder;
import org.springframework.security.oauth2.jwt.JwtException;
import org.springframework.transaction.annotation.Transactional;
import java.util.UUID;
import static org.junit.jupiter.api.Assertions.*;

/** Requiere MySQL de pruebas con el SQL incremental aplicado. Los registros se revierten. */
@SpringBootTest
@Transactional
class AuthSessionIntegrationTests {
    @Autowired RegistrationService registration;
    @Autowired AuthSessionService sessions;
    @Autowired JwtDecoder decoder;
    @Autowired TokenRevocationService revocations;

    @Test void rotacionReutilizacionYRevocacion() {
        String email = "auth-" + UUID.randomUUID() + "@example.invalid";
        long id = registration.register(new RegisterRequest("Usuario Prueba", email, "ClavePrueba123"));
        var first = sessions.login(email, "ClavePrueba123");
        assertEquals(Long.toString(id), decoder.decode(first.accessToken()).getSubject());
        var second = sessions.refresh(first.refreshToken());
        assertNotEquals(first.refreshToken(), second.refreshToken());
        assertThrows(JwtException.class, () -> decoder.decode(first.accessToken()));
        assertEquals(Long.toString(id), decoder.decode(second.accessToken()).getSubject());
        assertThrows(BadCredentialsException.class, () -> sessions.refresh(first.refreshToken()));
        assertThrows(JwtException.class, () -> decoder.decode(second.accessToken()));
        var third = sessions.login(email, "ClavePrueba123");
        sessions.logout(third.refreshToken());
        assertThrows(JwtException.class, () -> decoder.decode(third.accessToken()));
        assertThrows(BadCredentialsException.class, () -> sessions.refresh(third.refreshToken()));
        var fourth = sessions.login(email, "ClavePrueba123");
        revocations.revokeUser(id);
        assertThrows(JwtException.class, () -> decoder.decode(fourth.accessToken()));
    }
}
