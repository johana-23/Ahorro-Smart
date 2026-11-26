package com.smartahorro.smart_ahorro_api.controller;

import com.smartahorro.smart_ahorro_api.dto.AuthenticatedUserResponse;
import com.smartahorro.smart_ahorro_api.dto.LoginRequest;
import com.smartahorro.smart_ahorro_api.dto.RefreshRequest;
import com.smartahorro.smart_ahorro_api.dto.SessionResponse;
import com.smartahorro.smart_ahorro_api.service.AuthSessionService;
import com.smartahorro.smart_ahorro_api.service.AuthRateLimitService;
import jakarta.validation.Valid;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping(value = "/api/v1/auth", produces = MediaType.APPLICATION_JSON_VALUE)
public class AuthController {
    private final AuthSessionService sessions;
    private final AuthRateLimitService limits;
    public AuthController(AuthSessionService sessions, AuthRateLimitService limits) {
        this.sessions = sessions; this.limits = limits;
    }

    @PostMapping(value = "/login", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<SessionResponse> login(@Valid @RequestBody LoginRequest request) {
        limits.check("login", request.email(), 5, 900);
        return tokens(sessions.login(request.email(), request.password()));
    }

    @PostMapping(value = "/refresh", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<SessionResponse> refresh(@Valid @RequestBody RefreshRequest request) {
        limits.check("refresh", request.refreshToken(), 10, 60);
        return tokens(sessions.refresh(request.refreshToken()));
    }

    @GetMapping("/me")
    public ResponseEntity<AuthenticatedUserResponse> me(@AuthenticationPrincipal Jwt jwt) {
        return ResponseEntity.ok().header(HttpHeaders.CACHE_CONTROL, "no-store")
                .body(new AuthenticatedUserResponse(Long.parseLong(jwt.getSubject())));
    }

    // El refresh token permite cerrar la sesión incluso con el JWT vencido.
    @PostMapping(value = "/logout", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<Void> logout(@Valid @RequestBody RefreshRequest request) {
        limits.check("logout", request.refreshToken(), 20, 60);
        sessions.logout(request.refreshToken());
        return ResponseEntity.noContent().header(HttpHeaders.CACHE_CONTROL, "no-store").build();
    }

    private ResponseEntity<SessionResponse> tokens(SessionResponse body) {
        return ResponseEntity.ok().header(HttpHeaders.CACHE_CONTROL, "no-store")
                .header(HttpHeaders.PRAGMA, "no-cache").body(body);
    }
}
