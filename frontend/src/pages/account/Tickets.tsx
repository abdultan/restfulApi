import React, { useState } from "react";
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
import { Input } from "../../components/ui/input";
import { Label } from "../../components/ui/label";
import { DataTable } from "../../components/common/DataTable";
import { ConfirmDialog } from "../../components/common/ConfirmDialog";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "../../components/ui/dialog";
import { ticketsApi } from "../../api/tickets";
import { queryKeys } from "../../lib/queryClient";
import { formatDate, formatCurrency } from "../../lib/utils";
import {
    Calendar,
    MapPin,
    Download,
    Send,
    X,
    Ticket as TicketIcon,
} from "lucide-react";
import { Ticket, TicketFilters } from "../../types/api";

export function Tickets() {
    const [filters, setFilters] = useState<TicketFilters>({
        status: undefined,
        event_id: undefined,
        page: 1,
        per_page: 10,
    });

    const [cancelTicket, setCancelTicket] = useState<Ticket | null>(null);
    const [transferTicket, setTransferTicket] = useState<Ticket | null>(null);
    const [transferEmail, setTransferEmail] = useState("");
    const [cancelReason, setCancelReason] = useState("");

    const queryClient = useQueryClient();

    const { data: ticketsData, isLoading } = useQuery({
        queryKey: queryKeys.tickets(filters),
        queryFn: () => ticketsApi.getTickets(filters),
    });

    const cancelMutation = useMutation({
        mutationFn: ({ id, reason }: { id: number; reason?: string }) =>
            ticketsApi.cancelTicket(id, reason),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.tickets(filters),
            });
            toast.success("Ticket cancelled successfully");
            setCancelTicket(null);
            setCancelReason("");
        },
        onError: () => {
            toast.error("Failed to cancel ticket");
        },
    });

    const transferMutation = useMutation({
        mutationFn: ({ id, email }: { id: number; email: string }) =>
            ticketsApi.transferTicket(id, email),
        onSuccess: () => {
            queryClient.invalidateQueries({
                queryKey: queryKeys.tickets(filters),
            });
            toast.success("Ticket transferred successfully");
            setTransferTicket(null);
            setTransferEmail("");
        },
        onError: () => {
            toast.error("Failed to transfer ticket");
        },
    });

    const downloadTicket = async (ticketId: number) => {
        try {
            const blob = await ticketsApi.downloadTicket(ticketId);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `ticket-${ticketId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            toast.success("Ticket downloaded successfully");
        } catch (error) {
            toast.error("Failed to download ticket");
        }
    };

    // Etkinliğin tarihi geçmiş mi kontrol eden fonksiyon
    const isEventExpired = (ticket: Ticket) => {
        if (!ticket.event?.start_date) return false;
        const eventDate = new Date(ticket.event.start_date);
        const now = new Date();
        return eventDate < now;
    };

    const tickets = ticketsData?.data || [];
    const pagination = ticketsData
        ? {
              currentPage: ticketsData.current_page,
              totalPages: ticketsData.last_page,
              onPageChange: (page: number) =>
                  setFilters((prev) => ({ ...prev, page })),
          }
        : undefined;

    const getStatusBadge = (status: string) => {
        switch (status) {
            case "active":
                return <Badge variant="success">Active</Badge>;
            case "cancelled":
                return <Badge variant="destructive">Cancelled</Badge>;
            case "transferred":
                return <Badge variant="secondary">Transferred</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const columns = [
        {
            key: "id",
            header: "Ticket ID",
            render: (ticket: Ticket) => (
                <span className="font-mono text-sm">#{ticket.id}</span>
            ),
        },
        {
            key: "event",
            header: "Event",
            render: (ticket: Ticket) => (
                <div className="space-y-1">
                    <div className="font-medium">{ticket.event?.name}</div>
                    <div className="text-sm text-muted-foreground flex items-center gap-1">
                        <Calendar className="h-3 w-3" />
                        {ticket.event && formatDate(ticket.event.start_date)}
                    </div>
                    {ticket.event?.venue && (
                        <div className="text-sm text-muted-foreground flex items-center gap-1">
                            <MapPin className="h-3 w-3" />
                            {ticket.event.venue.name}
                        </div>
                    )}
                </div>
            ),
        },
        {
            key: "seat",
            header: "Seat",
            render: (ticket: Ticket) => (
                <div className="font-medium">{ticket.seat?.label}</div>
            ),
        },
        {
            key: "price",
            header: "Price",
            render: (ticket: Ticket) => (
                <div className="font-medium">
                    {formatCurrency(ticket.price)}
                </div>
            ),
        },
        {
            key: "status",
            header: "Status",
            render: (ticket: Ticket) => getStatusBadge(ticket.status),
        },
        {
            key: "created_at",
            header: "Purchased",
            render: (ticket: Ticket) => (
                <div className="text-sm text-muted-foreground">
                    {formatDate(ticket.created_at)}
                </div>
            ),
        },
        {
            key: "actions",
            header: "Actions",
            render: (ticket: Ticket) => {
                const expired = isEventExpired(ticket);

                return (
                    <div className="flex items-center gap-2">
                        {ticket.status === "active" && (
                            <>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => downloadTicket(ticket.id)}
                                >
                                    <Download className="h-3 w-3 mr-1" />
                                    Download
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setTransferTicket(ticket)}
                                    disabled={expired}
                                    className={
                                        expired
                                            ? "opacity-50 cursor-not-allowed"
                                            : ""
                                    }
                                    title={
                                        expired
                                            ? "Bu etkinliğin tarihi geçmiş olduğu için transfer yapılamaz"
                                            : ""
                                    }
                                >
                                    <Send className="h-3 w-3 mr-1" />
                                    Transfer
                                </Button>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setCancelTicket(ticket)}
                                    disabled={expired}
                                    className={
                                        expired
                                            ? "text-muted-foreground opacity-50 cursor-not-allowed"
                                            : "text-destructive hover:text-destructive"
                                    }
                                    title={
                                        expired
                                            ? "Bu etkinliğin tarihi geçmiş olduğu için iptal yapılamaz"
                                            : ""
                                    }
                                >
                                    <X className="h-3 w-3 mr-1" />
                                    Cancel
                                </Button>
                            </>
                        )}
                        {ticket.status === "cancelled" && (
                            <span className="text-sm text-muted-foreground">
                                No actions available
                            </span>
                        )}
                        {ticket.status === "active" && expired && (
                            <span className="text-sm text-muted-foreground">
                                Etkinlik sona erdi
                            </span>
                        )}
                    </div>
                );
            },
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Header */}
            <div className="space-y-4">
                <h1 className="text-3xl font-bold tracking-tight">
                    My Tickets
                </h1>
                <p className="text-muted-foreground">
                    View and manage your purchased tickets
                </p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <TicketIcon className="h-5 w-5 text-green-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        tickets.filter(
                                            (t) => t.status === "active"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Active Tickets
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Send className="h-5 w-5 text-blue-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        tickets.filter(
                                            (t) => t.status === "transferred"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Transferred
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <X className="h-5 w-5 text-destructive" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        tickets.filter(
                                            (t) => t.status === "cancelled"
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

            {/* Tickets Table */}
            <Card>
                <CardHeader>
                    <CardTitle>All Tickets</CardTitle>
                </CardHeader>
                <CardContent>
                    <DataTable
                        data={tickets}
                        columns={columns}
                        loading={isLoading}
                        pagination={pagination}
                        emptyMessage="You haven't purchased any tickets yet"
                    />
                </CardContent>
            </Card>

            {/* Cancel Dialog */}
            <Dialog
                open={!!cancelTicket}
                onOpenChange={() => setCancelTicket(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancel Ticket</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            Are you sure you want to cancel this ticket? This
                            action cannot be undone.
                        </p>
                        <div className="space-y-2">
                            <Label htmlFor="cancel-reason">
                                Reason (optional)
                            </Label>
                            <Input
                                id="cancel-reason"
                                placeholder="Enter cancellation reason..."
                                value={cancelReason}
                                onChange={(e) =>
                                    setCancelReason(e.target.value)
                                }
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setCancelTicket(null)}
                        >
                            Keep Ticket
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={() =>
                                cancelTicket &&
                                cancelMutation.mutate({
                                    id: cancelTicket.id,
                                    reason: cancelReason || undefined,
                                })
                            }
                            disabled={cancelMutation.isPending}
                        >
                            {cancelMutation.isPending
                                ? "Cancelling..."
                                : "Cancel Ticket"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Transfer Dialog */}
            <Dialog
                open={!!transferTicket}
                onOpenChange={() => setTransferTicket(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Transfer Ticket</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <p className="text-sm text-muted-foreground">
                            Enter the email address of the person you want to
                            transfer this ticket to.
                        </p>
                        <div className="space-y-2">
                            <Label htmlFor="transfer-email">
                                Recipient Email
                            </Label>
                            <Input
                                id="transfer-email"
                                type="email"
                                placeholder="Enter email address..."
                                value={transferEmail}
                                onChange={(e) =>
                                    setTransferEmail(e.target.value)
                                }
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setTransferTicket(null)}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={() =>
                                transferTicket &&
                                transferMutation.mutate({
                                    id: transferTicket.id,
                                    email: transferEmail,
                                })
                            }
                            disabled={
                                !transferEmail || transferMutation.isPending
                            }
                        >
                            {transferMutation.isPending
                                ? "Transferring..."
                                : "Transfer Ticket"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
