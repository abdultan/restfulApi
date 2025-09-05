import { useEffect, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { Button } from "../../components/ui/button";
import { Card, CardContent } from "../../components/ui/card";
import { Badge } from "../../components/ui/badge";
import { FiltersBar } from "../../components/common/FiltersBar";
// keep LoadingSpinner import only if used visually here
import { eventsApi } from "../../api/events";
import { venuesApi } from "../../api/venues";
import { queryKeys } from "../../lib/queryClient";
import { formatDate, getEventStatus } from "../../lib/utils";
import {
    CalendarDays,
    MapPin,
    Users,
    ChevronLeft,
    ChevronRight,
} from "lucide-react";
import { EventFilters } from "../../types/api";

export function EventsList() {
    const navigate = useNavigate();
    const location = useLocation();
    const [filters, setFilters] = useState<EventFilters>({
        search: "",
        venue_id: undefined,
        status: "",
        start_date: "",
        end_date: "",
        sort: "start_date",
        page: 1,
        per_page: 12,
    });

    const { data: eventsData, isLoading: eventsLoading } = useQuery({
        queryKey: queryKeys.events(filters),
        queryFn: () => eventsApi.getEvents(filters),
    });

    const { data: venues } = useQuery({
        queryKey: queryKeys.venues(),
        queryFn: venuesApi.getVenues,
    });

    const events = eventsData?.data || [];
    const pagination = eventsData
        ? {
              currentPage: eventsData.current_page,
              totalPages: eventsData.last_page,
              total: eventsData.total,
          }
        : null;

    const updateFilter = (key: keyof EventFilters, value: any) => {
        setFilters((prev) => ({
            ...prev,
            [key]: value,
            page: key !== "page" ? 1 : value, // Reset to page 1 when other filters change
        }));
    };

    // Parse URL query → state on URL change
    useEffect(() => {
        if (location.pathname !== "/events") return;
        const params = new URLSearchParams(location.search);
        const next: EventFilters = {
            search: params.get("search") || "",
            venue_id: params.get("venue_id")
                ? parseInt(params.get("venue_id") as string)
                : undefined,
            status: params.get("status") || "",
            start_date: params.get("start_date") || "",
            end_date: params.get("end_date") || "",
            sort: (params.get("sort") as any) || "start_date",
            page: params.get("page")
                ? parseInt(params.get("page") as string)
                : 1,
            per_page: params.get("per_page")
                ? parseInt(params.get("per_page") as string)
                : 12,
        };
        // Only update if different to avoid loops
        setFilters((prev) => {
            const changed = JSON.stringify(prev) !== JSON.stringify(next);
            return changed ? next : prev;
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [location.pathname, location.search]);

    // Keep URL in sync when filters change (without full reload)
    useEffect(() => {
        if (location.pathname !== "/events") return;
        const params = new URLSearchParams();
        if (filters.search) params.set("search", filters.search);
        if (filters.venue_id) params.set("venue_id", String(filters.venue_id));
        if (filters.status) params.set("status", filters.status);
        if (filters.start_date) params.set("start_date", filters.start_date);
        if (filters.end_date) params.set("end_date", filters.end_date);
        if (filters.sort) params.set("sort", String(filters.sort));
        if (filters.page && filters.page !== 1)
            params.set("page", String(filters.page));
        if (filters.per_page && filters.per_page !== 12)
            params.set("per_page", String(filters.per_page));

        const search = params.toString();
        const nextUrl = `/events${search ? `?${search}` : ""}`;
        if (nextUrl !== `${location.pathname}${location.search}`) {
            navigate(nextUrl, { replace: true });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters, location.pathname]);

    const clearFilters = () => {
        setFilters({
            search: "",
            venue_id: undefined,
            status: "",
            start_date: "",
            end_date: "",
            sort: "start_date",
            page: 1,
            per_page: 12,
        });
    };

    const hasActiveFilters = !!(
        filters.search ||
        filters.venue_id ||
        filters.status ||
        filters.start_date ||
        filters.end_date
    );

    const filterOptions = [
        {
            key: "venue_id",
            label: "Venue",
            value: filters.venue_id?.toString() || "",
            options:
                venues?.data?.map((venue) => ({
                    value: venue.id.toString(),
                    label: venue.name,
                })) || [],
            onChange: (value: string) =>
                updateFilter("venue_id", value ? parseInt(value) : undefined),
        },
        {
            key: "status",
            label: "Status",
            value: filters.status || "",
            options: [
                { value: "active", label: "Active" },
                { value: "inactive", label: "Inactive" },
                { value: "sold_out", label: "Sold Out" },
            ],
            onChange: (value: string) => updateFilter("status", value),
        },
        {
            key: "sort",
            label: "Sort By",
            value: filters.sort || "start_date",
            options: [
                { value: "start_date", label: "Date (Earliest)" },
                { value: "-start_date", label: "Date (Latest)" },
                { value: "name", label: "Name (A-Z)" },
                { value: "-name", label: "Name (Z-A)" },
            ],
            onChange: (value: string) => updateFilter("sort", value as any),
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Header */}
            <div className="space-y-4">
                <h1 className="text-3xl font-bold tracking-tight">
                    All Events
                </h1>
                <p className="text-muted-foreground">
                    Discover amazing events happening near you
                </p>
            </div>

            {/* Filters */}
            <FiltersBar
                searchTerm={filters.search || ""}
                onSearchChange={(value) => updateFilter("search", value)}
                filters={filterOptions}
                onClearFilters={clearFilters}
                hasActiveFilters={hasActiveFilters}
            />

            {/* Results Summary */}
            {pagination && (
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Showing {events.length} of {pagination.total} events
                    </span>
                    {hasActiveFilters && <span>Filters applied</span>}
                </div>
            )}

            {/* Events Grid */}
            {eventsLoading ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {Array.from({ length: 6 }).map((_, i) => (
                        <Card key={i} className="overflow-hidden">
                            <div className="aspect-[4/3] bg-muted animate-pulse" />
                            <CardContent className="p-6 space-y-3">
                                <div className="h-6 bg-muted animate-pulse rounded" />
                                <div className="h-4 bg-muted animate-pulse rounded w-3/4" />
                                <div className="h-4 bg-muted animate-pulse rounded w-1/2" />
                            </CardContent>
                        </Card>
                    ))}
                </div>
            ) : events.length === 0 ? (
                <div className="text-center py-12">
                    <CalendarDays className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <h3 className="text-lg font-medium mb-2">
                        No events found
                    </h3>
                    <p className="text-muted-foreground mb-4">
                        Try adjusting your search criteria or check back later.
                    </p>
                    {hasActiveFilters && (
                        <Button variant="outline" onClick={clearFilters}>
                            Clear Filters
                        </Button>
                    )}
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {events.map((event) => {
                        const eventStatus = getEventStatus(event);

                        return (
                            <Card
                                key={event.id}
                                className="overflow-hidden hover:shadow-lg transition-all duration-300 group"
                            >
                                <div className="aspect-[4/3] bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center group-hover:from-primary/30 group-hover:to-primary/20 transition-all duration-300">
                                    <CalendarDays className="h-16 w-16 text-primary/60" />
                                </div>
                                <CardContent className="p-6 space-y-4">
                                    <div className="space-y-2">
                                        <div className="flex items-start justify-between">
                                            <h3 className="font-semibold text-lg line-clamp-2 flex-1 group-hover:text-primary transition-colors">
                                                {event.name}
                                            </h3>
                                            <Badge
                                                variant={eventStatus.variant}
                                                className="ml-2 flex-shrink-0"
                                            >
                                                {eventStatus.status}
                                            </Badge>
                                        </div>
                                        <p className="text-muted-foreground text-sm line-clamp-2">
                                            {event.description}
                                        </p>
                                    </div>

                                    <div className="space-y-2 text-sm">
                                        <div className="flex items-center gap-2">
                                            <CalendarDays className="h-4 w-4 text-muted-foreground" />
                                            <span>
                                                {formatDate(event.start_date)}
                                            </span>
                                        </div>
                                        {event.venue && (
                                            <>
                                                <div className="flex items-center gap-2">
                                                    <MapPin className="h-4 w-4 text-muted-foreground" />
                                                    <span className="line-clamp-1">
                                                        {event.venue.name}
                                                    </span>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Users className="h-4 w-4 text-muted-foreground" />
                                                    <span>
                                                        Capacity:{" "}
                                                        {event.venue.capacity}
                                                    </span>
                                                </div>
                                            </>
                                        )}
                                    </div>

                                    <a
                                        href={`/events/${event.id}`}
                                        className="block"
                                    >
                                        <Button className="w-full group-hover:bg-primary/90 transition-colors">
                                            View Details & Book
                                        </Button>
                                    </a>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            )}

            {/* Pagination */}
            {pagination && pagination.totalPages > 1 && (
                <div className="flex items-center justify-center space-x-4">
                    <Button
                        variant="outline"
                        onClick={() =>
                            updateFilter(
                                "page",
                                Math.max(1, pagination.currentPage - 1)
                            )
                        }
                        disabled={pagination.currentPage <= 1}
                    >
                        <ChevronLeft className="h-4 w-4 mr-2" />
                        Previous
                    </Button>

                    <div className="flex items-center space-x-2">
                        <span className="text-sm text-muted-foreground">
                            Page {pagination.currentPage} of{" "}
                            {pagination.totalPages}
                        </span>
                    </div>

                    <Button
                        variant="outline"
                        onClick={() =>
                            updateFilter(
                                "page",
                                Math.min(
                                    pagination.totalPages,
                                    pagination.currentPage + 1
                                )
                            )
                        }
                        disabled={
                            pagination.currentPage >= pagination.totalPages
                        }
                    >
                        Next
                        <ChevronRight className="h-4 w-4 ml-2" />
                    </Button>
                </div>
            )}
        </div>
    );
}
