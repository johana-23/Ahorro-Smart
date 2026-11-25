package com.smartahorro.smart_ahorro_api;

import org.junit.jupiter.api.AfterEach;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.http.MediaType;
import org.springframework.http.converter.json.JacksonJsonHttpMessageConverter;
import org.springframework.test.web.servlet.MockMvc;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.content;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;
import org.springframework.test.web.servlet.setup.MockMvcBuilders;
import org.springframework.validation.beanvalidation.LocalValidatorFactoryBean;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.smartahorro.smart_ahorro_api.exception.GlobalExceptionHandler;

import jakarta.validation.Valid;
import jakarta.validation.constraints.NotBlank;
import tools.jackson.databind.json.JsonMapper;

class DtoValidationIntegrationTests {

    private MockMvc mockMvc;
    private LocalValidatorFactoryBean validator;

    @BeforeEach
    void setUp() {

        validator = new LocalValidatorFactoryBean();
        validator.afterPropertiesSet();

        JsonMapper jsonMapper = JsonMapper.builder().build();

        mockMvc = MockMvcBuilders
                .standaloneSetup(new ValidationTestController())
                .setControllerAdvice(new GlobalExceptionHandler())
                .setValidator(validator)
                .setMessageConverters(
                        new JacksonJsonHttpMessageConverter(
                                jsonMapper
                        )
                )
                .build();
    }

    @AfterEach
    void tearDown() {
        if (validator != null) {
            validator.close();
        }
    }

    @Test
    void campoVacioDebeResponder400() throws Exception {

        mockMvc.perform(
                post("/api/v1/test-only/validation")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"\"}")
        )
        .andExpect(status().isBadRequest())
        .andExpect(jsonPath("$.status").value(400))
        .andExpect(jsonPath("$.error").value("BAD_REQUEST"))
        .andExpect(jsonPath("$.message").value(
                "La solicitud contiene datos invalidos"
        ))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/test-only/validation"
        ));
    }

    @Test
    void campoAusenteDebeResponder400() throws Exception {

        mockMvc.perform(
                post("/api/v1/test-only/validation")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{}")
        )
        .andExpect(status().isBadRequest())
        .andExpect(jsonPath("$.status").value(400))
        .andExpect(jsonPath("$.error").value("BAD_REQUEST"))
        .andExpect(jsonPath("$.path").value(
                "/api/v1/test-only/validation"
        ));
    }

    
    @Test
    void campoValidoDebeResponder200() throws Exception {
    
        mockMvc.perform(
                post("/api/v1/test-only/validation")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"nombre\":\"Prueba\"}")
        )
        .andExpect(status().isOk())
        .andExpect(content().contentTypeCompatibleWith(
                MediaType.APPLICATION_JSON
        ))
        .andExpect(content().json("\"VALIDO\""));
    }


    @RestController
    @RequestMapping("/api/v1/test-only")
    static class ValidationTestController {

        @PostMapping(
                value = "/validation",
                consumes = MediaType.APPLICATION_JSON_VALUE
        )
        String validate(
                @Valid @RequestBody ValidationTestRequest request
        ) {
            return "VALIDO";
        }
    }

    public record ValidationTestRequest(
            @NotBlank String nombre
    ) {
    }
}