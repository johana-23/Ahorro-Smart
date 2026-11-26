package com.smartahorro.smart_ahorro_api.dto;

import jakarta.validation.Validation;
import org.junit.jupiter.api.Test;
import static org.junit.jupiter.api.Assertions.*;

class RefreshRequestTests {
    @Test void aceptaSoloTokenOpacoDe32BytesCodificado() {
        try (var factory = Validation.buildDefaultValidatorFactory()) {
            var validator = factory.getValidator();
            assertTrue(validator.validate(new RefreshRequest("A".repeat(43))).isEmpty());
            assertFalse(validator.validate(new RefreshRequest("A".repeat(42))).isEmpty());
            assertFalse(validator.validate(new RefreshRequest("!".repeat(43))).isEmpty());
            assertFalse(validator.validate(new RefreshRequest(null)).isEmpty());
        }
    }
}
