package com.smartahorro.smart_ahorro_api;

import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.boot.webmvc.test.autoconfigure.AutoConfigureMockMvc;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.content;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

@SpringBootTest
@AutoConfigureMockMvc

public class HealthControllerTests {
      @Autowired
    private MockMvc mockMvc;

    @Test
    void healthDebeResponder200() throws Exception {
        mockMvc.perform(get("/api/v1/health"))
                .andExpect(status().isOk());
    }

    @Test
    void healthDebeResponderJsonCorrecto() throws Exception {
        mockMvc.perform(get("/api/v1/health"))
                .andExpect(content().contentTypeCompatibleWith(
                        MediaType.APPLICATION_JSON))
                .andExpect(jsonPath("$.status").value("UP"))
                .andExpect(jsonPath("$.service").value("smart-ahorro-api"));
    }

    @Test
    void rutaProtegidaSinAutenticacionDebeResponder401()
            throws Exception {

        mockMvc.perform(get("/api/v1/prueba-seguridad"))
                .andExpect(status().isUnauthorized());
    }

    @Test
    void rutaProtegidaDebeResponderErrorJson()
            throws Exception {

        mockMvc.perform(get("/api/v1/prueba-seguridad"))
                .andExpect(content().contentTypeCompatibleWith(
                        MediaType.APPLICATION_JSON))
                .andExpect(jsonPath("$.error").value("UNAUTHORIZED"))
                .andExpect(jsonPath("$.message")
                        .value("Autenticacion requerida"));
    }
}
