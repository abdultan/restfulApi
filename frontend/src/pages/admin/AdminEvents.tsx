import React, { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Link, useLocation, useNavigate } from "react-router-dom";
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
import { Textarea } from "../../components/ui/textarea";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "../../components/ui/dialog";
import { DataTable } from "../../components/common/DataTable";
import { ConfirmDialog } from "../../components/common/ConfirmDialog";
import { eventsApi } from "../../api/events";
import { venuesApi } from "../../api/venues";
import { queryKeys } from "../../lib/queryClient";
import { formatDate, getEventStatus } from "../../lib/utils";
import { Calendar, MapPin, Plus, Edit, Trash2, ArrowLeft } from "lucide-react";
import { Event } from "../../types/api";

export function AdminEvents() {
    const location = useLocation();
    const navigate = useNavigate();
    const isCreateMode = location.pathname === "/admin/events/create";

    const [deleteEvent, setDeleteEvent] = useState<Event | null>(null);
    const [formData, setFormData] = useState({
        name: "",
        description: "",
        venue_id: "",
        start_date: "",
        end_date: "",
        status: "published",
    });

    const [editEvent, setEditEvent] = useState<Event | null>(null);

    const queryClient = useQueryClient();

    const { data: eventsData, isLoading } = useQuery({
        queryKey: queryKeys.events({ per_page: 50 }),
        queryFn: () => eventsApi.getEvents({ per_page: 50 }),
        enabled: !isCreateMode,
    });

    const { data: venues } = useQuery({
        queryKey: queryKeys.venues(),
        queryFn: venuesApi.getVenues,
        enabled: isCreateMode || !!editEvent,
    });

    const createMutation = useMutation({
        mutationFn: eventsApi.createEvent,
        onSuccess: () => {
            toast.success("Event created successfully");
            navigate("/admin/events");
        },
        onError: (error: any) => {
            toast.error(
                `Failed to create event: ${
                    error.response?.data?.message || error.message
                }`
            );
        },
    });

    const updateMutation = useMutation({
        mutationFn: ({ id, data }: { id: number; data: Partial<Event> }) =>
            eventsApi.updateEvent(id, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: queryKeys.events() });
            queryClient.invalidateQueries({ queryKey: ["events"] });
            toast.success("Event updated successfully");
            setEditEvent(null);
            setFormData({
                name: "",
                description: "",
                venue_id: "",
                start_date: "",
                end_date: "",
                status: "published",
            });
        },
        onError: () => {
            toast.error("Failed to update event");
        },
    });

    const deleteMutation = useMutation({
        mutationFn: eventsApi.deleteEvent,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: queryKeys.events({}) });
            toast.success("Event deleted successfully");
            setDeleteEvent(null);
        },
        onError: (error: any) => {
            console.error("Delete error:", error);
            toast.error(
                `Failed to delete event: ${
                    error.response?.data?.message || error.message
                }`
            );
        },
    });

    const events = eventsData?.data || [];
    const activeEvents = events.filter((e) => e.status === "published");

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        createMutation.mutate({
            ...formData,
            venue_id: parseInt(formData.venue_id),
        });
    };

    const handleInputChange = (field: string, value: string) => {
        setFormData((prev) => ({ ...prev, [field]: value }));
    };

    // Create mode render
    if (isCreateMode) {
        return (
            <div className="container mx-auto px-4 py-8 space-y-8">
                <div className="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        onClick={() => navigate("/admin/events")}
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Create Event
                        </h1>
                        <p className="text-muted-foreground">
                            Add a new event to the system
                        </p>
                    </div>
                </div>

                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Event Name</Label>
                                    <Input
                                        id="name"
                                        value={formData.name}
                                        onChange={(e) =>
                                            handleInputChange(
                                                "name",
                                                e.target.value
                                            )
                                        }
                                        placeholder="Enter event name"
                                        required
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="venue">Venue</Label>
                                    <select
                                        id="venue"
                                        value={formData.venue_id}
                                        onChange={(e) =>
                                            handleInputChange(
                                                "venue_id",
                                                e.target.value
                                            )
                                        }
                                        className="w-full p-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Select a venue</option>
                                        {venues?.data?.map((venue) => (
                                            <option
                                                key={venue.id}
                                                value={venue.id}
                                            >
                                                {venue.name} (Capacity:{" "}
                                                {venue.capacity})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="start_date">
                                        Start Date
                                    </Label>
                                    <Input
                                        id="start_date"
                                        type="datetime-local"
                                        value={formData.start_date}
                                        onChange={(e) =>
                                            handleInputChange(
                                                "start_date",
                                                e.target.value
                                            )
                                        }
                                        required
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="end_date">End Date</Label>
                                    <Input
                                        id="end_date"
                                        type="datetime-local"
                                        value={formData.end_date}
                                        onChange={(e) =>
                                            handleInputChange(
                                                "end_date",
                                                e.target.value
                                            )
                                        }
                                        required
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={formData.description}
                                    onChange={(e) =>
                                        handleInputChange(
                                            "description",
                                            e.target.value
                                        )
                                    }
                                    placeholder="Enter event description"
                                    rows={4}
                                />
                            </div>

                            <div className="flex gap-4">
                                <Button
                                    type="submit"
                                    disabled={createMutation.isPending}
                                >
                                    {createMutation.isPending
                                        ? "Creating..."
                                        : "Create Event"}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => navigate("/admin/events")}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        );
    }

    const columns = [
        {
            key: "id",
            header: "ID",
            render: (event: Event) => (
                <span className="font-mono text-sm">#{event.id}</span>
            ),
        },
        {
            key: "name",
            header: "Event Name",
            render: (event: Event) => (
                <div className="space-y-1">
                    <div className="font-medium">{event.name}</div>
                    <div className="text-sm text-muted-foreground line-clamp-1">
                        {event.description}
                    </div>
                </div>
            ),
        },
        {
            key: "venue",
            header: "Venue",
            render: (event: Event) => (
                <div className="space-y-1">
                    {event.venue ? (
                        <>
                            <div className="font-medium">
                                {event.venue.name}
                            </div>
                            <div className="text-sm text-muted-foreground flex items-center gap-1">
                                <MapPin className="h-3 w-3" />
                                Capacity: {event.venue.capacity}
                            </div>
                        </>
                    ) : (
                        <span className="text-muted-foreground">
                            No venue assigned
                        </span>
                    )}
                </div>
            ),
        },
        {
            key: "start_date",
            header: "Date",
            render: (event: Event) => (
                <div className="space-y-1">
                    <div className="font-medium">
                        {formatDate(event.start_date)}
                    </div>
                    <div className="text-sm text-muted-foreground">
                        to {formatDate(event.end_date)}
                    </div>
                </div>
            ),
        },
        {
            key: "status",
            header: "Status",
            render: (event: Event) => {
                const status = getEventStatus(event);
                return <Badge variant={status.variant}>{status.status}</Badge>;
            },
        },
        {
            key: "created_at",
            header: "Created",
            render: (event: Event) => (
                <div className="text-sm text-muted-foreground">
                    {formatDate(event.created_at)}
                </div>
            ),
        },
        {
            key: "actions",
            header: "Actions",
            render: (event: Event) => (
                <div className="flex items-center gap-2">
                    <Button variant="outline" size="sm" asChild>
                        <Link to={`/events/${event.id}`}>View</Link>
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setEditEvent(event)}
                    >
                        <Edit className="h-3 w-3 mr-1" />
                        Edit
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setDeleteEvent(event)}
                        className="text-destructive hover:text-destructive"
                    >
                        <Trash2 className="h-3 w-3 mr-1" />
                        Delete
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div className="space-y-1">
                    <h1 className="text-3xl font-bold tracking-tight">
                        Manage Events
                    </h1>
                    <p className="text-muted-foreground">
                        Create, edit, and manage all events in the system
                    </p>
                </div>
                <Button asChild>
                    <Link to="/admin/events/create">
                        <Plus className="h-4 w-4 mr-2" />
                        Create Event
                    </Link>
                </Button>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Calendar className="h-5 w-5 text-blue-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {events.length}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Total Events
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Calendar className="h-5 w-5 text-green-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {activeEvents.length}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Active Events
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Calendar className="h-5 w-5 text-yellow-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        events.filter(
                                            (e) => e.status === "sold_out"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Sold Out
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Calendar className="h-5 w-5 text-red-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {
                                        events.filter(
                                            (e) => e.status === "inactive"
                                        ).length
                                    }
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Inactive
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Events Table */}
            <Card>
                <CardHeader>
                    <CardTitle>All Events</CardTitle>
                </CardHeader>
                <CardContent>
                    <DataTable
                        data={events}
                        columns={columns}
                        loading={isLoading}
                        emptyMessage="No events created yet"
                    />
                </CardContent>
            </Card>

            {/* Edit Event Dialog */}
            {editEvent && (
                <Dialog
                    open={!!editEvent}
                    onOpenChange={() => setEditEvent(null)}
                >
                    <DialogContent className="max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Edit Event</DialogTitle>
                            <DialogDescription>
                                Update event information below
                            </DialogDescription>
                        </DialogHeader>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                const formElement = e.target as HTMLFormElement;
                                const formData = new FormData(formElement);
                                const data = {
                                    name: formData.get("name") as string,
                                    description: formData.get(
                                        "description"
                                    ) as string,
                                    venue_id: parseInt(
                                        formData.get("venue_id") as string
                                    ),
                                    start_date: formData.get(
                                        "start_date"
                                    ) as string,
                                    end_date: formData.get(
                                        "end_date"
                                    ) as string,
                                    status: formData.get("status") as string,
                                };
                                updateMutation.mutate({
                                    id: editEvent.id,
                                    data,
                                });
                            }}
                            className="space-y-6"
                        >
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="edit-name">
                                        Event Name
                                    </Label>
                                    <Input
                                        id="edit-name"
                                        name="name"
                                        defaultValue={editEvent.name}
                                        required
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="edit-venue">Venue</Label>
                                    <select
                                        id="edit-venue"
                                        name="venue_id"
                                        defaultValue={editEvent.venue.id}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        required
                                    >
                                        {venues?.data?.map((venue) => (
                                            <option
                                                key={venue.id}
                                                value={venue.id}
                                            >
                                                {venue.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-description">
                                    Description
                                </Label>
                                <Textarea
                                    id="edit-description"
                                    name="description"
                                    defaultValue={editEvent.description}
                                    rows={3}
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="edit-start-date">
                                        Start Date
                                    </Label>
                                    <Input
                                        id="edit-start-date"
                                        name="start_date"
                                        type="datetime-local"
                                        defaultValue={new Date(
                                            editEvent.start_date
                                        )
                                            .toISOString()
                                            .slice(0, 16)}
                                        required
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="edit-end-date">
                                        End Date
                                    </Label>
                                    <Input
                                        id="edit-end-date"
                                        name="end_date"
                                        type="datetime-local"
                                        defaultValue={new Date(
                                            editEvent.end_date
                                        )
                                            .toISOString()
                                            .slice(0, 16)}
                                        required
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-status">Status</Label>
                                <select
                                    id="edit-status"
                                    name="status"
                                    defaultValue={editEvent.status}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setEditEvent(null)}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={updateMutation.isPending}
                                >
                                    {updateMutation.isPending
                                        ? "Updating..."
                                        : "Update Event"}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            )}

            {/* Delete Confirmation Dialog */}
            <ConfirmDialog
                open={!!deleteEvent}
                onOpenChange={() => setDeleteEvent(null)}
                title="Delete Event"
                description={`Are you sure you want to delete "${deleteEvent?.name}"? This action cannot be undone and will affect all related reservations and tickets.`}
                confirmText="Delete Event"
                onConfirm={() =>
                    deleteEvent && deleteMutation.mutate(deleteEvent.id)
                }
                variant="destructive"
                loading={deleteMutation.isPending}
            />
        </div>
    );
}
