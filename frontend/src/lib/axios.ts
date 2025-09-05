import axios from "axios";
import { toast } from "react-hot-toast";
import { useAuthStore } from "../stores/auth";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
    headers: {
        "Content-Type": "application/json",
    },
});

api.interceptors.request.use((config) => {
    const token = useAuthStore.getState().token;
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

let isRefreshing = false as boolean;
let refreshWaiters: Array<(token: string | null) => void> = [];

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        if (error.response?.status === 401 && !originalRequest._retry) {
            // Do not attempt token refresh for explicit logout requests
            const isLogoutRequest =
                typeof originalRequest?.url === "string" &&
                originalRequest.url.includes("/auth/logout");
            if (isLogoutRequest) {
                useAuthStore.getState().logout();
                return Promise.reject(error);
            }
            originalRequest._retry = true;

            try {
                const currentToken = useAuthStore.getState().token;
                if (!currentToken) {
                    useAuthStore.getState().logout();
                    window.location.href = "/login";
                    return Promise.reject(error);
                }

                if (isRefreshing) {
                    const newToken = await new Promise<string | null>(
                        (resolve) => {
                            refreshWaiters.push(resolve);
                        }
                    );
                    if (!newToken) return Promise.reject(error);
                    originalRequest.headers.Authorization = `Bearer ${newToken}`;
                    return api(originalRequest);
                }

                isRefreshing = true;
                const response = await api.post("/auth/refresh");
                const newToken =
                    response.data?.access_token || response.data?.token;
                if (!newToken)
                    throw new Error("No access_token in refresh response");

                useAuthStore.getState().setToken(newToken);
                refreshWaiters.forEach((resolve) => resolve(newToken));
                refreshWaiters = [];
                isRefreshing = false;

                originalRequest.headers.Authorization = `Bearer ${newToken}`;
                return api(originalRequest);
            } catch (refreshError) {
                refreshWaiters.forEach((resolve) => resolve(null));
                refreshWaiters = [];
                isRefreshing = false;
                useAuthStore.getState().logout();
                window.location.href = "/login";
                return Promise.reject(refreshError);
            }
        }

        // Handle common error scenarios
        if (error.response?.status === 403) {
            toast.error("Access denied");
        } else if (error.response?.status >= 500) {
            toast.error("Server error occurred");
        } else if (error.response?.data?.message) {
            toast.error(error.response.data.message);
        }

        return Promise.reject(error);
    }
);

export default api;
