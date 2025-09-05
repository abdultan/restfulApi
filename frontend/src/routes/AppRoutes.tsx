import React, { Suspense, lazy, useEffect } from "react";
import { Routes, Route, Navigate, useLocation } from "react-router-dom";
import { Layout } from "../components/layout/Layout";
import { LoadingSpinner } from "../components/common/LoadingSpinner";
import { ProtectedRoute } from "./ProtectedRoute";
import { RoleGuard } from "./RoleGuard";
import { ErrorBoundary } from "../components/common/ErrorBoundary";
import { EventDetail } from "../pages/events/EventDetail";

// Lazy load pages
const Home = lazy(() =>
    import("../pages/Home").then((m) => ({ default: m.Home }))
);
const EventsList = lazy(() =>
    import("../pages/events/EventsList").then((m) => ({
        default: m.EventsList,
    }))
);
const Login = lazy(() =>
    import("../pages/auth/Login").then((m) => ({ default: m.Login }))
);
const Register = lazy(() =>
    import("../pages/auth/Register").then((m) => ({ default: m.Register }))
);
const VerifyEmail = lazy(() =>
    import("../pages/auth/VerifyEmail").then((m) => ({
        default: m.VerifyEmail,
    }))
);
const Reservations = lazy(() =>
    import("../pages/account/Reservations").then((m) => ({
        default: m.Reservations,
    }))
);
const ReservationDetail = lazy(() =>
    import("../pages/account/ReservationDetail").then((m) => ({
        default: m.ReservationDetail,
    }))
);
const Tickets = lazy(() =>
    import("../pages/account/Tickets").then((m) => ({ default: m.Tickets }))
);
const AdminDashboard = lazy(() =>
    import("../pages/admin/AdminDashboard").then((m) => ({
        default: m.AdminDashboard,
    }))
);
const AdminEvents = lazy(() =>
    import("../pages/admin/AdminEvents").then((m) => ({
        default: m.AdminEvents,
    }))
);
const AdminVenues = lazy(() =>
    import("../pages/admin/AdminVenues").then((m) => ({
        default: m.AdminVenues,
    }))
);

// Loading fallback
const PageLoader = () => (
    <div className="flex items-center justify-center min-h-[400px]">
        <LoadingSpinner size="lg" />
    </div>
);

export function AppRoutes() {
    const location = useLocation();

    // Optional: scroll to top on route change for visual feedback
    useEffect(() => {
        if (typeof window !== "undefined") {
            window.scrollTo({ top: 0, behavior: "instant" as ScrollBehavior });
        }
    }, [location.pathname]);

    return (
        <ErrorBoundary>
            <Layout>
                <Suspense fallback={<PageLoader />}>
                    <Routes location={location} key={location.pathname}>
                        {/* Public Routes */}
                        <Route path="/" element={<Home />} />
                        <Route path="/events" element={<EventsList />} />
                        <Route path="/events/:id" element={<EventDetail />} />

                        {/* Auth Routes */}
                        <Route path="/login" element={<Login />} />
                        <Route path="/register" element={<Register />} />
                        <Route path="/verify-email" element={<VerifyEmail />} />

                        {/* Protected Routes */}
                        <Route
                            path="/account/reservations"
                            element={
                                <ProtectedRoute>
                                    <Reservations />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/account/reservations/:id"
                            element={
                                <ProtectedRoute>
                                    <ReservationDetail />
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/account/tickets"
                            element={
                                <ProtectedRoute>
                                    <Tickets />
                                </ProtectedRoute>
                            }
                        />

                        {/* Admin Routes */}
                        <Route
                            path="/admin"
                            element={
                                <ProtectedRoute>
                                    <RoleGuard roles={["admin"]}>
                                        <AdminDashboard />
                                    </RoleGuard>
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/admin/events"
                            element={
                                <ProtectedRoute>
                                    <RoleGuard roles={["admin"]}>
                                        <AdminEvents />
                                    </RoleGuard>
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/admin/venues"
                            element={
                                <ProtectedRoute>
                                    <RoleGuard roles={["admin"]}>
                                        <AdminVenues />
                                    </RoleGuard>
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/admin/events/create"
                            element={
                                <ProtectedRoute>
                                    <RoleGuard roles={["admin"]}>
                                        <AdminEvents />
                                    </RoleGuard>
                                </ProtectedRoute>
                            }
                        />
                        <Route
                            path="/admin/venues/create"
                            element={
                                <ProtectedRoute>
                                    <RoleGuard roles={["admin"]}>
                                        <AdminVenues />
                                    </RoleGuard>
                                </ProtectedRoute>
                            }
                        />

                        {/* Catch all */}
                        <Route path="*" element={<Navigate to="/" replace />} />
                    </Routes>
                </Suspense>
            </Layout>
        </ErrorBoundary>
    );
}
