import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { toast } from "react-hot-toast";
import { Button } from "../../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
import { Badge } from "../../components/ui/badge";
import { Alert, AlertDescription } from "../../components/ui/alert";
import { SeatMap } from "../../components/seatmap/SeatMap";
import { LoadingSpinner } from "../../components/common/LoadingSpinner";
import { eventsApi } from "../../api/events";
import { seatsApi } from "../../api/seats";
import { reservationsApi } from "../../api/reservations";
import { useAuth } from "../../hooks/useAuth";
import { useTimer } from "../../hooks/useTimer";
import { queryKeys } from "../../lib/queryClient";
import {
    formatDateTime,
    formatCurrency,
    getEventStatus,
} from "../../lib/utils";
import {
    CalendarDays,
    MapPin,
    Clock,
    Users,
    AlertCircle,
    Ticket,
} from "lucide-react";

export function EventDetail() {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { isAuthenticated } = useAuth();
    const queryClient = useQueryClient();

    const [selectedSeats, setSelectedSeats] = useState<number[]>([]);
    const [isBlocking, setIsBlocking] = useState(false);
    const [blockedUntil, setBlockedUntil] = useState<Date | null>(null);

    const eventId = parseInt(id || "0");

    // Fetch event details
    const { data: event, isLoading: eventLoading } = useQuery({
        queryKey: queryKeys.event(eventId),
        queryFn: () => eventsApi.getEvent(eventId),
        enabled: !!eventId,
    });

    // Fetch seats
    const {
        data: seats = [],
        isLoading: seatsLoading,
        refetch: refetchSeats,
    } = useQuery({
        queryKey: queryKeys.seats(eventId),
        queryFn: () => seatsApi.getEventSeats(eventId),
        enabled: !!eventId,
        refetchInterval: 30000, // Refresh every 30 seconds
    });

    // Timer for blocked seats
    const {
        isActive: timerActive,
        formattedTime,
        start: startTimer,
        reset: resetTimer,
    } = useTimer({
        initialTime: 900, // 15 minutes
        onExpire: () => {
            handleReleaseSeats();
            toast.error("Your seat selection has expired");
        },
    });

    // Restore ongoing block from localStorage on mount (survives refresh)
    useEffect(() => {
        const key = `blockedUntil:${eventId}`;
        const ts = localStorage.getItem(key);
        if (ts) {
            const until = new Date(parseInt(ts, 10));
            const remaining = Math.max(
                0,
                Math.floor((until.getTime() - Date.now()) / 1000)
            );
            if (remaining > 0) {
                setIsBlocking(true);
                setBlockedUntil(until);
                resetTimer(remaining);
                startTimer();
                // restore selected seats
                const sel = localStorage.getItem(`selectedSeats:${eventId}`);
                if (sel) {
                    try {
                        const ids = JSON.parse(sel) as number[];
                        if (Array.isArray(ids)) setSelectedSeats(ids);
                    } catch {}
                }
            } else {
                localStorage.removeItem(key);
                localStorage.removeItem(`selectedSeats:${eventId}`);
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [eventId]);

    // Block seats mutation
    const blockSeatsMutation = useMutation({
        mutationFn: () => seatsApi.blockSeats(eventId, selectedSeats),
        onSuccess: () => {
            setIsBlocking(true);
            const until = new Date(Date.now() + 15 * 60 * 1000);
            setBlockedUntil(until);
            // persist for refresh-resilience
            localStorage.setItem(
                `blockedUntil:${eventId}`,
                String(until.getTime())
            );
            localStorage.setItem(
                `selectedSeats:${eventId}`,
                JSON.stringify(selectedSeats)
            );
            startTimer();
            refetchSeats();
            toast.success("Seats blocked for 15 minutes");
        },
        onError: () => {
            toast.error("Failed to block seats");
        },
    });

    // Create reservation mutation
    const createReservationMutation = useMutation({
        mutationFn: () =>
            reservationsApi.createReservation(eventId, selectedSeats),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.reservations(),
            });
            toast.success("Reservation created successfully!");
            navigate(`/account/reservations`);
        },
        onError: () => {
            toast.error("Failed to create reservation");
        },
    });

    const handleSeatSelect = (seatIds: number[]) => {
        if (isBlocking && seatIds.length !== selectedSeats.length) {
            // Don't allow seat changes while blocking is active
            return;
        }
        setSelectedSeats(seatIds);
    };

    const handleBlockSeats = () => {
        if (!isAuthenticated) {
            toast.error("Please login to select seats");
            navigate("/login");
            return;
        }

        if (selectedSeats.length === 0) {
            toast.error("Please select at least one seat");
            return;
        }

        blockSeatsMutation.mutate();
    };

    const handleReleaseSeats = async () => {
        if (selectedSeats.length > 0) {
            try {
                await seatsApi.releaseSeats(selectedSeats);
                refetchSeats();
            } catch (error) {
                console.error("Failed to release seats:", error);
            }
        }
        setSelectedSeats([]);
        setIsBlocking(false);
        setBlockedUntil(null);
        localStorage.removeItem(`blockedUntil:${eventId}`);
        localStorage.removeItem(`selectedSeats:${eventId}`);
        resetTimer();
    };

    const handleCreateReservation = () => {
        createReservationMutation.mutate();
        localStorage.removeItem(`blockedUntil:${eventId}`);
        localStorage.removeItem(`selectedSeats:${eventId}`);
    };

    if (eventLoading || seatsLoading) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="flex items-center justify-center min-h-[400px]">
                    <LoadingSpinner size="lg" />
                </div>
            </div>
        );
    }

    if (!event) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="text-center space-y-4">
                    <h1 className="text-2xl font-bold">Event not found</h1>
                    <Button onClick={() => navigate("/events")}>
                        Back to Events
                    </Button>
                </div>
            </div>
        );
    }

    const eventStatus = getEventStatus(event);
    const selectedSeatDetails = seats.filter((seat) =>
        selectedSeats.includes(seat.id)
    );
    const totalPrice = selectedSeatDetails.reduce(
        (sum, seat) => sum + seat.price,
        0
    );

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Event Header */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div className="lg:col-span-2 space-y-6">
                    <div className="space-y-4">
                        <div className="flex items-start justify-between">
                            <h1 className="text-3xl font-bold tracking-tight">
                                {event.name}
                            </h1>
                            <Badge variant={eventStatus.variant}>
                                {eventStatus.status}
                            </Badge>
                        </div>

                        <p className="text-muted-foreground text-lg">
                            {event.description}
                        </p>
                    </div>

                    {/* Event Details */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center gap-3">
                            <CalendarDays className="h-5 w-5 text-primary" />
                            <div>
                                <div className="font-medium">Start Date</div>
                                <div className="text-sm text-muted-foreground">
                                    {formatDateTime(event.start_date)}
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <Clock className="h-5 w-5 text-primary" />
                            <div>
                                <div className="font-medium">End Date</div>
                                <div className="text-sm text-muted-foreground">
                                    {formatDateTime(event.end_date)}
                                </div>
                            </div>
                        </div>

                        {event.venue && (
                            <>
                                <div className="flex items-center gap-3">
                                    <MapPin className="h-5 w-5 text-primary" />
                                    <div>
                                        <div className="font-medium">
                                            {event.venue.name}
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {event.venue.address}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3">
                                    <Users className="h-5 w-5 text-primary" />
                                    <div>
                                        <div className="font-medium">
                                            Capacity
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {event.venue.capacity} seats
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Booking Summary */}
                <div className="space-y-4">
                    {timerActive && (
                        <Alert>
                            <Clock className="h-4 w-4" />
                            <AlertDescription>
                                <div className="font-medium">
                                    Seats reserved
                                </div>
                                <div className="text-sm">
                                    Time remaining:{" "}
                                    <strong>{formattedTime}</strong>
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    {selectedSeats.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Ticket className="h-5 w-5" />
                                    Booking Summary
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2 text-sm">
                                    <div className="font-medium">
                                        {selectedSeats.length} seat(s) selected
                                    </div>
                                    <div className="space-y-1">
                                        {selectedSeatDetails.map((seat) => (
                                            <div
                                                key={seat.id}
                                                className="flex justify-between"
                                            >
                                                <span>{seat.label}</span>
                                                <span>
                                                    {formatCurrency(seat.price)}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="border-t pt-4">
                                    <div className="flex justify-between font-semibold">
                                        <span>Total</span>
                                        <span>
                                            {formatCurrency(totalPrice)}
                                        </span>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    {!isBlocking ? (
                                        <Button
                                            onClick={handleBlockSeats}
                                            disabled={
                                                selectedSeats.length === 0 ||
                                                blockSeatsMutation.isPending
                                            }
                                            className="w-full"
                                        >
                                            {blockSeatsMutation.isPending ? (
                                                <>
                                                    <LoadingSpinner
                                                        size="sm"
                                                        className="mr-2"
                                                    />
                                                    Blocking Seats...
                                                </>
                                            ) : (
                                                "Block Seats (15 min)"
                                            )}
                                        </Button>
                                    ) : (
                                        <div className="space-y-2">
                                            <Button
                                                onClick={
                                                    handleCreateReservation
                                                }
                                                disabled={
                                                    createReservationMutation.isPending
                                                }
                                                className="w-full"
                                            >
                                                {createReservationMutation.isPending ? (
                                                    <>
                                                        <LoadingSpinner
                                                            size="sm"
                                                            className="mr-2"
                                                        />
                                                        Creating Reservation...
                                                    </>
                                                ) : (
                                                    "Complete Reservation"
                                                )}
                                            </Button>
                                            <Button
                                                variant="outline"
                                                onClick={handleReleaseSeats}
                                                className="w-full"
                                            >
                                                Cancel Selection
                                            </Button>
                                        </div>
                                    )}
                                </div>

                                {!isAuthenticated && (
                                    <Alert>
                                        <AlertCircle className="h-4 w-4" />
                                        <AlertDescription>
                                            <Button
                                                variant="link"
                                                onClick={() =>
                                                    navigate("/login")
                                                }
                                                className="p-0"
                                            >
                                                Login
                                            </Button>{" "}
                                            to book tickets
                                        </AlertDescription>
                                    </Alert>
                                )}
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>

            {/* Seat Map */}
            <Card>
                <CardHeader>
                    <CardTitle>Select Your Seats</CardTitle>
                </CardHeader>
                <CardContent>
                    <SeatMap
                        seats={seats}
                        selectedSeats={selectedSeats}
                        onSeatSelect={handleSeatSelect}
                        capacity={event.venue?.capacity}
                        disabled={
                            eventStatus.status === "Sold Out" ||
                            eventStatus.status === "Past"
                        }
                    />
                </CardContent>
            </Card>
        </div>
    );
}
