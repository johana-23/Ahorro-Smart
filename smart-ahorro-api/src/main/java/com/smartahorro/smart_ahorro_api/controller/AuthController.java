package com.smartahorro.smart_ahorro_api.controller;

import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.security.oauth2.jwt.Jwt;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.smartahorro.smart_ahorro_api.dto.AuthenticatedUserResponse;
import com.smartahorro.smart_ahorro_api.dto.LoginRequest;
import com.smartahorro.smart_ahorro_api.dto.LoginResponse;
import com.smartahorro.smart_ahorro_api.service.AuthService;
import com.smartahorro.smart_ahorro_api.service.JwtService;
import com.smartahorro.smart_ahorro_api.service.TokenRevocationService;

import jakarta.validation.Valid;

@RestController
@RequestMapping(value = "/api/v1/auth", produces = MediaType.APPLICATION_JSON_VALUE)
public class AuthController {

    private final AuthService authService;
    private final JwtService jwtService;
    private final TokenRevocationService revocationService;

    public AuthController(AuthService authService,
            JwtService jwtService,
            TokenRevocationService revocationService) {
        this.authService = authService;
        this.jwtService = jwtService;
        this.revocationService = revocationService;
    }

    @PostMapping(value = "/login", consumes = MediaType.APPLICATION_JSON_VALUE)
    public ResponseEntity<LoginResponse> login(@Valid @RequestBody LoginRequest request) {
        Long userId = authService.authenticate(request.email(), request.password());
        LoginResponse result = jwtService.issueAccessToken(userId);
        return ResponseEntity.ok()
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .header(HttpHeaders.PRAGMA, "no-cache")
                .body(result);
    }

    @GetMapping("/me")
    public ResponseEntity<AuthenticatedUserResponse> me(@AuthenticationPrincipal Jwt jwt) {
        return ResponseEntity.ok()
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .body(new AuthenticatedUserResponse(Long.parseLong(jwt.getSubject())));
    }

    @PostMapping("/logout")
    public ResponseEntity<Void> logout(@AuthenticationPrincipal Jwt jwt) {
        revocationService.revoke(jwt.getId(), jwt.getExpiresAt());
        return ResponseEntity.noContent()
                .header(HttpHeaders.CACHE_CONTROL, "no-store")
                .build();
    }
}
