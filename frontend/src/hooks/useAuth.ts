import { useMutation, useQuery } from "@tanstack/react-query";
import { toast } from "react-hot-toast";
import { useNavigate } from "react-router-dom";
import { authApi } from "../api/auth";
import { useAuthStore } from "../stores/auth";
import { queryKeys } from "../lib/queryClient";
import { LoginCredentials, RegisterCredentials } from "../types/api";

export const useAuth = () => {
    const {
        user,
        isAuthenticated,
        setAuth,
        logout: logoutStore,
    } = useAuthStore();
    const navigate = useNavigate();

    const loginMutation = useMutation({
        mutationFn: authApi.login,
        onSuccess: (data) => {
            setAuth(data.user, data.token);
            toast.success("Logged in successfully");
            navigate("/");
        },
        onError: () => {
            toast.error("Invalid credentials");
        },
    });

    const registerMutation = useMutation({
        mutationFn: authApi.register,
        onSuccess: (data) => {
            toast.success(
                data?.message ||
                    "Verification link sent. Please verify your email."
            );
            // Do NOT authenticate here; require email verification + manual login
            navigate("/login");
        },
        onError: () => {
            toast.error("Registration failed");
        },
    });

    const logoutMutation = useMutation({
        mutationFn: authApi.logout,
        onSuccess: () => {
            logoutStore();
            toast.success("Logged out successfully");
            navigate("/");
        },
        onError: () => {
            // Still logout locally even if server request fails
            logoutStore();
            navigate("/");
        },
    });

    const verifyEmailMutation = useMutation({
        mutationFn: ({ email, token }: { email: string; token: string }) =>
            authApi.verifyEmail(email, token),
        onSuccess: () => {
            toast.success("Email verified successfully");
        },
        onError: () => {
            toast.error("Email verification failed");
        },
    });

    const resendVerificationMutation = useMutation({
        mutationFn: authApi.resendVerification,
        onSuccess: () => {
            toast.success("Verification email sent");
        },
        onError: () => {
            toast.error("Failed to send verification email");
        },
    });

    const userQuery = useQuery({
        queryKey: queryKeys.user(),
        queryFn: authApi.getProfile,
        enabled: isAuthenticated,
        staleTime: 10 * 60 * 1000, // 10 minutes
    });

    return {
        user,
        isAuthenticated,
        login: loginMutation.mutate,
        register: registerMutation.mutate,
        logout: logoutMutation.mutate,
        verifyEmail: verifyEmailMutation.mutate,
        resendVerification: resendVerificationMutation.mutate,
        isLoading: loginMutation.isPending || registerMutation.isPending,
        isVerifying: verifyEmailMutation.isPending,
    };
};
