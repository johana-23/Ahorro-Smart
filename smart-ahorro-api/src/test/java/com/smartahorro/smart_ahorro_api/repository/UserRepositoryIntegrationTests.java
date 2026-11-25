package com.smartahorro.smart_ahorro_api.repository;

import java.util.UUID;

import static org.junit.jupiter.api.Assertions.assertTrue;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.transaction.annotation.Transactional;

@SpringBootTest
@Transactional(readOnly = true)
class UserRepositoryIntegrationTests {

    @Autowired
    private UserRepository userRepository;

    @Test
    void buscarCorreoInexistenteDebeRetornarOptionalVacio() {

        String correoInexistente =
                "prueba-" + UUID.randomUUID()
                + "@example.invalid";

        boolean resultadoVacio = userRepository
                .findByEmail(correoInexistente)
                .isEmpty();

        assertTrue(
                resultadoVacio,
                "La consulta de un correo inexistente debe retornar Optional.empty()"
        );
    }
}