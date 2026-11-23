package com.smartahorro.smart_ahorro_api.dto;

import org.junit.jupiter.api.AfterAll;
import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertTrue;
import org.junit.jupiter.api.BeforeAll;
import org.junit.jupiter.api.Test;

import jakarta.validation.Validation;
import jakarta.validation.Validator;
import jakarta.validation.ValidatorFactory;

class LoginDtoTests {

    private static ValidatorFactory factory;
    private static Validator validator;

    @BeforeAll
    static void setUp() {
        factory = Validation.buildDefaultValidatorFactory();
        validator = factory.getValidator();
    }

    @AfterAll
    static void tearDown() {
        factory.close();
    }

    @Test
    void debeAceptarSolicitudValida() {
        LoginRequest request = new LoginRequest(
                "usuario@ejemplo.com",
                "ClaveSegura123!"
        );

        assertTrue(validator.validate(request).isEmpty());
    }

    @Test
    void debeRechazarCorreoVacio() {
        LoginRequest request = new LoginRequest(
                "",
                "ClaveSegura123!"
        );

        assertFalse(validator.validate(request).isEmpty());
    }

    @Test
    void debeRechazarCorreoInvalido() {
        LoginRequest request = new LoginRequest(
                "correo-invalido",
                "ClaveSegura123!"
        );

        assertFalse(validator.validate(request).isEmpty());
    }

    @Test
    void debeRechazarContrasenaVacia() {
        LoginRequest request = new LoginRequest(
                "usuario@ejemplo.com",
                ""
        );

        assertFalse(validator.validate(request).isEmpty());
    }

    @Test
    void debeRepresentarRespuestaDeAcceso() {
        LoginResponse response = new LoginResponse(
                "token-de-prueba",
                "Bearer",
                900
        );

        assertEquals("token-de-prueba", response.accessToken());
        assertEquals("Bearer", response.tokenType());
        assertEquals(900, response.expiresIn());
    }
}