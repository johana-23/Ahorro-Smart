package com.smartahorro.smart_ahorro_api.security;
import com.smartahorro.smart_ahorro_api.service.TokenRevocationService;
import org.junit.jupiter.api.Test;
import org.springframework.jdbc.core.JdbcTemplate;
import static org.junit.jupiter.api.Assertions.*;
import static org.mockito.ArgumentMatchers.*;
import static org.mockito.Mockito.*;
class TokenRevocationServiceTests {
    private final JdbcTemplate jdbc = mock(JdbcTemplate.class);
    private final TokenRevocationService service = new TokenRevocationService(jdbc);
    @Test void soloRegistroActivoSeAcepta() {
        when(jdbc.queryForObject(anyString(), eq(Integer.class), eq("jti"), eq(1L))).thenReturn(1, 0);
        assertTrue(service.isActive("jti", 1L));
        assertFalse(service.isActive("jti", 1L));
    }
    @Test void rechazaIdentificadorAusente() {
        assertFalse(service.isActive(null, 1L));
        assertFalse(service.isActive("jti", 0L));
        verifyNoInteractions(jdbc);
    }
    @Test void revocaFamiliaEnPersistencia() {
        service.revokeFamily("familia");
        verify(jdbc).update(contains("revoked_at"), eq("familia"));
    }
}
