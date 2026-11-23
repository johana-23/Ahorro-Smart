package com.smartahorro.smart_ahorro_api;

import com.smartahorro.smart_ahorro_api.controller.HealthController;
import com.smartahorro.smart_ahorro_api.exception.GlobalExceptionHandler;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;

import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.setup.MockMvcBuilders;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.server.ResponseStatusException;
import org.springframework.http.HttpStatus;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

class HttpExceptionIntegrationTests {

    private MockMvc mockMvc;

    @BeforeEach
    void setUp() {
        mockMvc = MockMvcBuilders
                .standaloneSetup(
                        new HealthController(),
                        new TestController()
                )
                .setControllerAdvice(
                        new GlobalExceptionHandler()
                )
                .build();
    }

    @Test
    void metodoNoPermitidoDebeResponder405() throws Exception {

        mockMvc.perform(
                post("/api/v1/health")
        )
        .andExpect(status().isMethodNotAllowed())
        .andExpect(content().contentTypeCompatibleWith(
                MediaType.APPLICATION_JSON
        ))
        .andExpect(jsonPath("$.status").value(405))
        .andExpect(jsonPath("$.error").value(
                "METHOD_NOT_ALLOWED"
        ))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/health"
        ));
    }

    @Test
    void recursoNoEncontradoDebeResponder404() throws Exception {

        mockMvc.perform(
                get("/api/v1/test-only/not-found")
        )
        .andExpect(status().isNotFound())
        .andExpect(jsonPath("$.status").value(404))
        .andExpect(jsonPath("$.error").value(
                "NOT_FOUND"
        ))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/test-only/not-found"
        ));
    }

    @Test
    void jsonInvalidoDebeResponder400() throws Exception {

        mockMvc.perform(
                post("/api/v1/test-only/body")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{json-invalido}")
        )
        .andExpect(status().isBadRequest())
        .andExpect(jsonPath("$.status").value(400))
        .andExpect(jsonPath("$.error").value(
                "BAD_REQUEST"
        ))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/test-only/body"
        ));
    }

    @Test
    void contenidoNoCompatibleDebeResponder415() throws Exception {

        mockMvc.perform(
                post("/api/v1/test-only/body")
                        .contentType(MediaType.TEXT_PLAIN)
                        .content("texto de prueba")
        )
        .andExpect(status().isUnsupportedMediaType())
        .andExpect(jsonPath("$.status").value(415))
        .andExpect(jsonPath("$.error").value(
                "UNSUPPORTED_MEDIA_TYPE"
        ))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/test-only/body"
        ));
    }

    @RestController
    @RequestMapping("/api/v1/test-only")
    static class TestController {

        @GetMapping("/not-found")
        String notFound() {
            throw new ResponseStatusException(
                    HttpStatus.NOT_FOUND,
                    "Recurso de prueba inexistente"
            );
        }

        @PostMapping(
                value = "/body",
                consumes = MediaType.APPLICATION_JSON_VALUE
        )
        String receiveBody(
                @RequestBody TestRequest request
        ) {
            return request.value();
        }
    }

    record TestRequest(String value) {
    }
}