package com.smartahorro.smart_ahorro_api.dto;

public record ApiErrorResponse(
        int status,
        String error,
        String message,
        String path
)
{}
