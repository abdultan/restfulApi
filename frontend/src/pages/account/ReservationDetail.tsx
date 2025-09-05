import React from "react";
import { useParams, useNavigate, Link } from "react-router-dom";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { reservationsApi } from "../../api/reservations";
import { queryKeys } from "../../lib/queryClient";
import { Button } from "../../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
import { LoadingSpinner } from "../../components/common/LoadingSpinner";
import { formatCurrency, formatDate } from "../../lib/utils";

export function ReservationDetail() {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const resId = Number(id);

    const { data: reservation, isLoading } = useQuery({
        queryKey: ["reservation", resId],
        queryFn: () => reservationsApi.getReservation(resId),
        enabled: !!resId,
    });

    const confirmMutation = useMutation({
        mutationFn: () => reservationsApi.confirmReservation(resId),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.reservations(),
            });
            navigate("/account/reservations");
        },
    });

    const deleteMutation = useMutation({
        mutationFn: () => reservationsApi.deleteReservation(resId),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.reservations(),
            });
            navigate("/account/reservations");
        },
    });

    if (isLoading || !reservation) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="flex items-center justify-center min-h-[300px]">
                    <LoadingSpinner size="lg" />
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8 space-y-6">
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold">
                    Reservation #{reservation.id}
                </h1>
                <Button variant="outline" asChild>
                    <Link to="/account/reservations">Back</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Summary</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm">
                    <div>
                        Status: <strong>{reservation.status}</strong>
                    </div>
                    <div>Created: {formatDate(reservation.created_at)}</div>
                    {reservation.expires_at && (
                        <div>Expires: {formatDate(reservation.expires_at)}</div>
                    )}
                    <div>
                        Total:{" "}
                        <strong>
                            {formatCurrency(reservation.total_price)}
                        </strong>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Seats</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2 text-sm">
                    {reservation.seats?.map((s) => (
                        <div key={s.id} className="flex justify-between">
                            <span>{s.seat?.label}</span>
                            <span>{formatCurrency(s.price)}</span>
                        </div>
                    ))}
                </CardContent>
            </Card>

            <div className="flex gap-2">
                {reservation.status === "pending" && (
                    <Button
                        onClick={() => confirmMutation.mutate()}
                        disabled={confirmMutation.isPending}
                    >
                        {confirmMutation.isPending
                            ? "Confirming..."
                            : "Confirm Reservation"}
                    </Button>
                )}
                <Button
                    variant="outline"
                    onClick={() => deleteMutation.mutate()}
                    disabled={deleteMutation.isPending}
                >
                    {deleteMutation.isPending
                        ? "Cancelling..."
                        : "Cancel Reservation"}
                </Button>
            </div>
        </div>
    );
}

export default ReservationDetail;
