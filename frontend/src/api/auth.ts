import api from "../lib/axios";
import {
    User,
    AuthResponse,
    LoginCredentials,
    RegisterCredentials,
} from "../types/api";

export const authApi = {
    login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
        const response = await api.post("/auth/login", credentials);
        // Backend returns { status, user, access_token, type }
        const d = response.data;
        return { user: d.user, token: d.access_token } as any;
    },

    register: async (
        credentials: RegisterCredentials
    ): Promise<{ message?: string }> => {
        const response = await api.post("/auth/register", credentials);
        return response.data;
    },

    logout: async (): Promise<void> => {
        await api.post("/auth/logout");
    },

    refresh: async (): Promise<{ token: string }> => {
        const response = await api.post("/auth/refresh");
        const d = response.data;
        return { token: d.access_token };
    },

    verifyEmail: async (email: string, token: string): Promise<void> => {
        await api.post("/auth/verify-email", { email, token });
    },

    resendVerification: async (): Promise<void> => {
        await api.post("/auth/resend-email-verification-link");
    },

    getProfile: async (): Promise<User> => {
        const response = await api.get("/auth/user");
        return response.data.data;
    },
};
