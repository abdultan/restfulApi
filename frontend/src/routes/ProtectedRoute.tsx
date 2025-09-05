import React, { useEffect, useState } from "react";
import { Navigate, useLocation } from "react-router-dom";
import { useAuthStore } from "../stores/auth";

interface ProtectedRouteProps {
    children: React.ReactNode;
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
    const { isAuthenticated } = useAuthStore();
    const location = useLocation();

    // Wait until zustand persist hydration completes
    const initialHydrated =
        (useAuthStore as any)?.persist?.hasHydrated?.() ?? true;
    const [hydrated, setHydrated] = useState<boolean>(initialHydrated);

    useEffect(() => {
        const api = (useAuthStore as any)?.persist;
        const unsubStart = api?.onHydrate?.(() => setHydrated(false));
        const unsubEnd = api?.onFinishHydration?.(() => setHydrated(true));
        return () => {
            unsubStart?.();
            unsubEnd?.();
        };
    }, []);

    if (!hydrated) {
        return null;
    }

    if (!isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    return <>{children}</>;
}
