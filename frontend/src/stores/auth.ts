import { create } from "zustand";
import { persist } from "zustand/middleware";
import { User } from "../types/api";

interface AuthState {
    user: User | null;
    token: string | null;
    refreshToken: string | null;
    isAuthenticated: boolean;
    setAuth: (user: User, token: string, refreshToken?: string) => void;
    setToken: (token: string) => void;
    logout: () => void;
    updateUser: (user: Partial<User>) => void;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set, get) => ({
            user: null,
            token: null,
            refreshToken: null,
            isAuthenticated: false,

            setAuth: (user, token, refreshToken) => {
                set({
                    user,
                    token,
                    refreshToken,
                    isAuthenticated: true,
                });
            },

            setToken: (token) => {
                set({ token });
            },

            logout: () => {
                set({
                    user: null,
                    token: null,
                    refreshToken: null,
                    isAuthenticated: false,
                });
                try {
                    if (typeof window !== "undefined") {
                        window.localStorage.removeItem("auth-storage");
                        window.sessionStorage.removeItem("auth-storage");
                    }
                } catch {}
            },

            updateUser: (userData) => {
                const currentUser = get().user;
                if (currentUser) {
                    set({
                        user: { ...currentUser, ...userData },
                    });
                }
            },
        }),
        {
            name: "auth-storage",
            partialize: (state) => ({
                user: state.user,
                token: state.token,
                refreshToken: state.refreshToken,
                isAuthenticated: state.isAuthenticated,
            }),
        }
    )
);
