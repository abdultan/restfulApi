import { create } from "zustand";
import { persist } from "zustand/middleware";

interface UIState {
    theme: "light" | "dark";
    sidebarOpen: boolean;
    toggleTheme: () => void;
    setSidebarOpen: (open: boolean) => void;
}

export const useUIStore = create<UIState>()(
    persist(
        (set, get) => ({
            theme: "light",
            sidebarOpen: false,

            toggleTheme: () => {
                const newTheme = get().theme === "light" ? "dark" : "light";
                set({ theme: newTheme });

                // Update document class
                if (newTheme === "dark") {
                    document.documentElement.classList.add("dark");
                } else {
                    document.documentElement.classList.remove("dark");
                }
            },

            setSidebarOpen: (open) => set({ sidebarOpen: open }),
        }),
        {
            name: "ui-storage",
            partialize: (state) => ({ theme: state.theme }),
        }
    )
);
