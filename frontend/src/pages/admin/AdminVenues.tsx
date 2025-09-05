import React, { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "react-hot-toast";
import { useLocation, useNavigate } from "react-router-dom";
import { Button } from "../../components/ui/button";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
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
import { venuesApi } from "../../api/venues";
import { queryKeys } from "../../lib/queryClient";
import { formatDate } from "../../lib/utils";
import { MapPin, Plus, Edit, Trash2, Users } from "lucide-react";
import { Venue } from "../../types/api";

const venueSchema = z.object({
    name: z.string().min(2, "Name must be at least 2 characters"),
    address: z.string().min(5, "Address must be at least 5 characters"),
    capacity: z.number().min(1, "Capacity must be at least 1"),
});

type VenueForm = z.infer<typeof venueSchema>;

export function AdminVenues() {
    const location = useLocation();
    const navigate = useNavigate();
    const isCreateMode = location.pathname === "/admin/venues/create";
    const [editVenue, setEditVenue] = useState<Venue | null>(null);
    const [deleteVenue, setDeleteVenue] = useState<Venue | null>(null);
    const [showCreateForm, setShowCreateForm] = useState(false);

    const queryClient = useQueryClient();

    const { data: venuesResponse, isLoading } = useQuery({
        queryKey: queryKeys.venues(),
        queryFn: venuesApi.getVenues,
    });

    const venues = venuesResponse?.data || [];

    const {
        register,
        handleSubmit,
        formState: { errors },
        reset,
        setValue,
    } = useForm<VenueForm>({
        resolver: zodResolver(venueSchema),
    });

    const createMutation = useMutation({
        mutationFn: venuesApi.createVenue,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: queryKeys.venues() });
            toast.success("Venue created successfully");
            setShowCreateForm(false);
            reset();
        },
        onError: () => {
            toast.error("Failed to create venue");
        },
    });

    const updateMutation = useMutation({
        mutationFn: ({ id, data }: { id: number; data: Partial<Venue> }) =>
            venuesApi.updateVenue(id, data),
        onSuccess: () => {
            // Invalidate venues cache
            queryClient.invalidateQueries({ queryKey: queryKeys.venues() });
            // Also invalidate events cache since venue info is embedded
            queryClient.invalidateQueries({ queryKey: ["events"] });
            toast.success("Venue updated successfully");
            setEditVenue(null);
            reset();
        },
        onError: () => {
            toast.error("Failed to update venue");
        },
    });

    const deleteMutation = useMutation({
        mutationFn: venuesApi.deleteVenue,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: queryKeys.venues() });
            toast.success("Venue deleted successfully");
            setDeleteVenue(null);
        },
        onError: () => {
            toast.error("Failed to delete venue");
        },
    });

    const onSubmit = (data: VenueForm) => {
        if (editVenue) {
            updateMutation.mutate({ id: editVenue.id, data });
        } else {
            createMutation.mutate(data);
        }
    };

    const handleEdit = (venue: Venue) => {
        setEditVenue(venue);
        setValue("name", venue.name);
        setValue("address", venue.address);
        setValue("capacity", venue.capacity);
    };

    const handleCloseForm = () => {
        setEditVenue(null);
        setShowCreateForm(false);
        reset();
    };

    const columns = [
        {
            key: "id",
            header: "ID",
            render: (venue: Venue) => (
                <span className="font-mono text-sm">#{venue.id}</span>
            ),
        },
        {
            key: "name",
            header: "Venue Name",
            render: (venue: Venue) => (
                <div className="font-medium">{venue.name}</div>
            ),
        },
        {
            key: "address",
            header: "Address",
            render: (venue: Venue) => (
                <div className="text-sm text-muted-foreground line-clamp-2">
                    {venue.address}
                </div>
            ),
        },
        {
            key: "capacity",
            header: "Capacity",
            render: (venue: Venue) => (
                <div className="flex items-center gap-1">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">
                        {venue.capacity.toLocaleString()}
                    </span>
                </div>
            ),
        },
        {
            key: "created_at",
            header: "Created",
            render: (venue: Venue) => (
                <div className="text-sm text-muted-foreground">
                    {formatDate(venue.created_at)}
                </div>
            ),
        },
        {
            key: "actions",
            header: "Actions",
            render: (venue: Venue) => (
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => handleEdit(venue)}
                    >
                        <Edit className="h-3 w-3 mr-1" />
                        Edit
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setDeleteVenue(venue)}
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
                        Manage Venues
                    </h1>
                    <p className="text-muted-foreground">
                        Add and manage venue information
                    </p>
                </div>
                <Button onClick={() => setShowCreateForm(true)}>
                    <Plus className="h-4 w-4 mr-2" />
                    Add Venue
                </Button>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <MapPin className="h-5 w-5 text-primary" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {venues.length}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Total Venues
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Users className="h-5 w-5 text-green-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {venues
                                        .reduce(
                                            (sum, venue) =>
                                                sum + venue.capacity,
                                            0
                                        )
                                        .toLocaleString()}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Total Capacity
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-6">
                        <div className="flex items-center space-x-2">
                            <Users className="h-5 w-5 text-blue-500" />
                            <div>
                                <div className="text-2xl font-bold">
                                    {venues.length > 0
                                        ? Math.round(
                                              venues.reduce(
                                                  (sum, venue) =>
                                                      sum + venue.capacity,
                                                  0
                                              ) / venues.length
                                          )
                                        : 0}
                                </div>
                                <div className="text-sm text-muted-foreground">
                                    Avg. Capacity
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Venues Table */}
            <Card>
                <CardHeader>
                    <CardTitle>All Venues</CardTitle>
                </CardHeader>
                <CardContent>
                    <DataTable
                        data={venues}
                        columns={columns}
                        loading={isLoading}
                        emptyMessage="No venues created yet"
                    />
                </CardContent>
            </Card>

            {/* Create/Edit Form Dialog */}
            <Dialog
                open={showCreateForm || !!editVenue}
                onOpenChange={handleCloseForm}
            >
                <DialogContent className="sm:max-w-[500px]">
                    <DialogHeader>
                        <DialogTitle>
                            {editVenue ? "Edit Venue" : "Create New Venue"}
                        </DialogTitle>
                    </DialogHeader>
                    <form
                        onSubmit={handleSubmit(onSubmit)}
                        className="space-y-4"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="name">Venue Name</Label>
                            <Input
                                id="name"
                                placeholder="Enter venue name"
                                {...register("name")}
                            />
                            {errors.name && (
                                <p className="text-sm text-destructive">
                                    {errors.name.message}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="address">Address</Label>
                            <Input
                                id="address"
                                placeholder="Enter venue address"
                                {...register("address")}
                            />
                            {errors.address && (
                                <p className="text-sm text-destructive">
                                    {errors.address.message}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="capacity">Capacity</Label>
                            <Input
                                id="capacity"
                                type="number"
                                placeholder="Enter venue capacity"
                                {...register("capacity", {
                                    valueAsNumber: true,
                                })}
                            />
                            {errors.capacity && (
                                <p className="text-sm text-destructive">
                                    {errors.capacity.message}
                                </p>
                            )}
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleCloseForm}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                disabled={
                                    createMutation.isPending ||
                                    updateMutation.isPending
                                }
                            >
                                {createMutation.isPending ||
                                updateMutation.isPending
                                    ? "Saving..."
                                    : editVenue
                                    ? "Update Venue"
                                    : "Create Venue"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <ConfirmDialog
                open={!!deleteVenue}
                onOpenChange={() => setDeleteVenue(null)}
                title="Delete Venue"
                description={`Are you sure you want to delete "${deleteVenue?.name}"? This action cannot be undone and may affect related events.`}
                confirmText="Delete Venue"
                onConfirm={() =>
                    deleteVenue && deleteMutation.mutate(deleteVenue.id)
                }
                variant="destructive"
                loading={deleteMutation.isPending}
            />
        </div>
    );
}
