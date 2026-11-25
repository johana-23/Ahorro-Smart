package com.smartahorro.smart_ahorro_api;

import java.nio.charset.StandardCharsets;
import java.util.Set;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assertions.assertFalse;
import static org.junit.jupiter.api.Assertions.assertNotNull;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.http.converter.HttpMessageNotReadableException;
import org.springframework.mock.http.MockHttpInputMessage;
import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.web.server.ResponseStatusException;

import com.smartahorro.smart_ahorro_api.dto.ApiErrorResponse;
import com.smartahorro.smart_ahorro_api.exception.GlobalExceptionHandler;

import jakarta.servlet.http.HttpServletRequest;
import jakarta.validation.ConstraintViolationException;

class GlobalExceptionHandlerTests {

    private GlobalExceptionHandler handler;
    private HttpServletRequest request;

    @BeforeEach
    void setUp() {
        handler = new GlobalExceptionHandler();

        MockHttpServletRequest mockRequest =
                new MockHttpServletRequest();

        mockRequest.setRequestURI("/api/v1/prueba");

        request = mockRequest;
    }

    @Test
    void jsonInvalidoDebeResponder400() {

        MockHttpInputMessage inputMessage =
                new MockHttpInputMessage(
                        "{json-invalido}".getBytes(
                                StandardCharsets.UTF_8
                        )
                );

        HttpMessageNotReadableException exception =
                new HttpMessageNotReadableException(
                        "JSON invalido",
                        inputMessage
                );

        ResponseEntity<ApiErrorResponse> response =
                handler.handleInvalidBody(
                        exception,
                        request
                );

        assertEquals(
                HttpStatus.BAD_REQUEST,
                response.getStatusCode()
        );

        assertNotNull(response.getBody());

        assertEquals(400, response.getBody().status());

        assertEquals(
                "BAD_REQUEST",
                response.getBody().error()
        );

        assertEquals(
                "/api/v1/prueba",
                response.getBody().path()
        );
    }

    @Test
    void restriccionesInvalidasDebenResponder400() {

        ConstraintViolationException exception =
                new ConstraintViolationException(
                        "Datos invalidos",
                        Set.of()
                );

        ResponseEntity<ApiErrorResponse> response =
                handler.handleConstraintViolation(
                        exception,
                        request
                );

        assertEquals(
                HttpStatus.BAD_REQUEST,
                response.getStatusCode()
        );

        assertNotNull(response.getBody());

        assertEquals(400, response.getBody().status());

        assertEquals(
                "BAD_REQUEST",
                response.getBody().error()
        );
    }

    @Test
    void conflictoDebeConservarEstado409() {

        ResponseStatusException exception =
                new ResponseStatusException(
                        HttpStatus.CONFLICT,
                        "Conflicto de prueba"
                );

        ResponseEntity<ApiErrorResponse> response =
                handler.handleResponseStatus(
                        exception,
                        request
                );

        assertEquals(
                HttpStatus.CONFLICT,
                response.getStatusCode()
        );

        assertNotNull(response.getBody());

        assertEquals(409, response.getBody().status());

        assertEquals(
                "CONFLICT",
                response.getBody().error()
        );
    }

    @Test
    void recursoInexistenteDebeConservarEstado404() {

        ResponseStatusException exception =
                new ResponseStatusException(
                        HttpStatus.NOT_FOUND,
                        "Recurso inexistente"
                );

        ResponseEntity<ApiErrorResponse> response =
                handler.handleResponseStatus(
                        exception,
                        request
                );

        assertEquals(
                HttpStatus.NOT_FOUND,
                response.getStatusCode()
        );

        assertNotNull(response.getBody());

        assertEquals(404, response.getBody().status());

        assertEquals(
                "NOT_FOUND",
                response.getBody().error()
        );
    }

    @Test
    void errorInesperadoDebeResponder500() {

        Exception exception =
                new IllegalStateException(
                        "Detalle interno de prueba"
                );

        ResponseEntity<ApiErrorResponse> response =
                handler.handleUnexpectedException(
                        exception,
                        request
                );

        assertEquals(
                HttpStatus.INTERNAL_SERVER_ERROR,
                response.getStatusCode()
        );

        assertNotNull(response.getBody());

        assertEquals(500, response.getBody().status());

        assertEquals(
                "INTERNAL_SERVER_ERROR",
                response.getBody().error()
        );

        assertFalse(
                response.getBody()
                        .message()
                        .contains("Detalle interno de prueba")
        );
    }
}