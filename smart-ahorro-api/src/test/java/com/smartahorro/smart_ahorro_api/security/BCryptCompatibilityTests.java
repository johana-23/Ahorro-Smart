package com.smartahorro.smart_ahorro_api.security;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;
import org.junit.jupiter.api.Test;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;

class BCryptCompatibilityTests {

    private final BCryptPasswordEncoder passwordEncoder =
            new BCryptPasswordEncoder(12);

    @Test
    void debeVerificarUnaContrasenaCorrecta() {

        String password = "ClaveSegura123!";
        String hash = passwordEncoder.encode(password);

        assertTrue(passwordEncoder.matches(password, hash));
    }

    @Test
    void debeRechazarUnaContrasenaIncorrecta() {

        String password = "ClaveSegura123!";
        String hash = passwordEncoder.encode(password);

        assertFalse(
                passwordEncoder.matches("ClaveIncorrecta123!", hash)
        );
    }

    @Test
    void debeVerificarUnHashConPrefijoLaravel() {

        String password = "ClaveSegura123!";

        BCryptPasswordEncoder encoderLaravel =
                new BCryptPasswordEncoder(
                        BCryptPasswordEncoder.BCryptVersion.$2Y,
                        12
                );

        String hashLaravel = encoderLaravel.encode(password);

        assertTrue(hashLaravel.startsWith("$2y$"));

        assertTrue(
                passwordEncoder.matches(password, hashLaravel)
        );
    }

    @Test
    void debeGenerarHashesConCostoDoce() {

        String hash = passwordEncoder.encode("ClaveSegura123!");

        String[] partes = hash.split("\\$");

        assertEquals("12", partes[2]);
    }
}