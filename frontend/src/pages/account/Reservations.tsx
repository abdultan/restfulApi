import { useEffect, useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useNavigate } from "react-router-dom";
import { toast } from "react-hot-toast";
import { Button } from "../../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
import { Badge } from "../../components/ui/badge";
import { DataTable } from "../../components/common/DataTable";
import { ConfirmDialog } from "../../components/common/ConfirmDialog";
import { reservationsApi } from "../../api/reservations";
import { queryKeys } from "../../lib/queryClient";
import { formatDate, formatCurrency } from "../../lib/utils";
import { Calendar, Clock, MapPin, Ticket, Trash2, Eye } from "lucide-react";
import { Reservation } from "../../types/api";

export function Reservations() {
    const navigate = useNavigate();
    const [deleteReservation, setDeleteReservation] =
        useState<Reservation | null>(null);
    const queryClient = useQueryClient();

    // Ticker for live countdowns (updates every second)
    const [now, setNow] = useState<number>(Date.now());
    useEffect(() => {
        const t = setInterval(() => setNow(Date.now()), 1000);
        return () => clearInterval(t);
    }, []);

    const { data: reservations = [], isLoading } = useQuery({
        queryKey: queryKeys.reservations(),
        queryFn: reservationsApi.getReservations,
    });

    const deleteMutation = useMutation({
        mutationFn: reservationsApi.deleteReservation,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.reservations(),
            });
            toast.success("Reservation cancelled successfully");
            setDeleteReservation(null);
        },
        onError: () => {
            toast.error("Failed to cancel reservation");
        },
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case "confirmed":
                return <Badge variant="success">Confirmed</Badge>;
            case "pending":
                return <Badge variant="default">Pending</Badge>;
            case "cancelled":
                return <Badge variant="destructive">Cancelled</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const resolveExpiry = (reservation: Reservation): number | null => {
        // Prefer server-provided expires_at
        if (reservation.expires_at) {
            const t = new Date(reservation.expires_at).getTime();
            if (!Number.isNaN(t)) return t;
        }
        // Fallback to client-persisted block timestamp (for current user's fresh block)
        const key = `blockedUntil:${
            reservation.event_id || reservation.event?.id
        }`;
        const raw =
            typeof window !== "undefined" ? localStorage.getItem(key) : null;
        if (raw) {
            const tt = parseInt(raw, 10);
            if (!Number.isNaN(tt)) return tt;
        }
        return null;
    };

    const isExpired = (reservation: Reservation) => {
        const ts = resolveExpiry(reservation);
        if (!ts) return true;
        return ts <= now;
    };

    const formatRemaining = (reservation: Reservation) => {
        const ts = resolveExpiry(reservation);
        if (!ts) return "";
        const diffMs = ts - now;
        if (diffMs <= 0) return "Expired";
        const totalSec = Math.floor(diffMs / 1000);
        const m = Math.floor(totalSec / 60);
        const s = totalSec % 60;
        const mm = String(m).padStart(2, "0");
        const ss = String(s).padStart(2, "0");
        return `${mm}:${ss}`;
    };

    const confirmMutation = useMutation({
        mutationFn: reservationsApi.confirmReservation,
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.reservations(),
            });
        },
    });

    const columns = [
        {
            key: "id",
            header: "ID",
            render: (reservation: Reservation) => (
                <span className="font-mono text-sm">#{reservation.id}</span>
            ),
        },
        {
            key: "event",
            header: "Event",
            render: (reservation: Reservation) => (
                <div className="space-y-1">
                    <div className="font-medium">{reservation.event?.name}</div>
                    <div className="text-sm text-muted-foreground flex items-center gap-1">
                        <Calendar className="h-3 w-3" />
                        {reservation.event &&
                            formatDate(reservation.event.start_date)}
                    </div>
                    {reservation.event?.venue && (
                        <div className="text-sm text-muted-foreground flex items-center gap-1">
                            <MapPin className="h-3 w-3" />
                            {reservation.event.venue.name}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: "seats",
            header: "Seats",
            render: (reservation: Reservation) => (
                <div className="space-y-1">
                    <div className="font-medium">
                        {reservation.seats?.length || 0} seats
                    </div>
                    <div className="text-sm text-muted-foreground">
                        {reservation.seats
                            ?.map((rs) => rs.seat?.label)
                            .join(", ")}
                    </div>
                </div>
            ),
        },
        {
            key: "total_price",
            header: "Total",
            render: (reservation: Reservation) => (
                <div className="font-medium">
                    {formatCurrency(reservation.total_price)}
                </div>
            ),
        },
        {
            key: "status",
            header: "Status",
            render: (reservation: Reservation) => (
                <div className="space-y-1">
                    {getStatusBadge(reservation.status)}
                    {reservation.status === "pending" && (
                        <div className="text-xs text-muted-foreground flex items-center gap-1">
                            <Clock className="h-3 w-3" />
                            Time left: {formatRemaining(reservation)}
                            {isExpired(reservation) && (
                                <span className="text-destructive font-medium">
                                    (Expired)
                                </span>
                            )}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: "created_at",
            header: "Created",
            render: (reservation: Reservation) => (
                <div className="text-sm text-muted-foreground">
                    {formatDate(reservation.created_at)}
                </div>
            ),
        },
        {
            key: "actions",
            header: "Actions",
            render: (reservation: Reservation) => (
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            navigate(`/account/reservations/${reservation.id}`)
                        }
                    >
                        <Eye className="h-3 w-3 mr-1" />
                        View
                    </Button>
                    {reservation.status === "pending" &&
                        !isExpired(reservation) && (
                            <Button
                                size="sm"
                                onClick={() =>
                                    confirmMutation.mutate(reservation.id)
                                }
                                disabled={confirmMutation.isPending}
                            >
                                <Ticket className="h-3 w-3 mr-1" />
                                Confirm
                            </Button>
                        )}
                    {reservation.status !== "cancelled" && (
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setDeleteReservation(reservation)}
                            className="text-destructive hover:text-destructive"
                        >
                            <Trash2 className="h-3 w-3 mr-1" />
                            Cancel
                        </Button>
                    )}
                </div>
            ),
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Header */}
            <div className="space-y-4">
                <h1 className="text-3xl font-bold tracking-tight">
                    My Reservations
                </h1>
                <p className="text-muted-foreground">
                    Manage your event reservations and bookings
                </p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Ticket className="h-5 w-5 text-primary" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        reservations.filter(
                                            (r) => r.status === "confirmed"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Confirmed
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Clock className="h-5 w-5 text-yellow-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        reservations.filter(
                                            (r) => r.status === "pending"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Pending
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Trash2 className="h-5 w-5 text-destructive" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        reservations.filter(
                                            (r) => r.status === "cancelled"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Cancelled
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Reservations Table */}
            <Card>
                <CardHeader>
                    <CardTitle>All Reservations</CardTitle>
                </CardHeader>
                <CardContent>
                    <DataTable
                        data={reservations}
                        columns={columns}
                        loading={isLoading}
                        emptyMessage="You haven't made any reservations yet"
                    />
                </CardContent>
            </Card>

            {/* Delete Confirmation Dialog */}
            <ConfirmDialog
                open={!!deleteReservation}
                onOpenChange={() => setDeleteReservation(null)}
                title="Cancel Reservation"
                description={`Are you sure you want to cancel this reservation? This action cannot be undone.`}
                confirmText="Cancel Reservation"
                onConfirm={() =>
                    deleteReservation &&
                    deleteMutation.mutate(deleteReservation.id)
                }
                variant="destructive"
                loading={deleteMutation.isPending}
            />
        </div>
    );
}
