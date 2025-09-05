import React, { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { Link, useNavigate } from "react-router-dom";
import { Button } from "../components/ui/button";
import { Card, CardContent } from "../components/ui/card";
import { Badge } from "../components/ui/badge";
import { Input } from "../components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "../components/ui/select";
import { LoadingSpinner } from "../components/common/LoadingSpinner";
import { eventsApi } from "../api/events";
import { venuesApi } from "../api/venues";
import { queryKeys } from "../lib/queryClient";
import { formatDate, getEventStatus } from "../lib/utils";
import { CalendarDays, MapPin, Search, Users } from "lucide-react";

export function Home() {
    const navigate = useNavigate();
    const [searchTerm, setSearchTerm] = useState("");
    const [selectedVenue, setSelectedVenue] = useState<string>("all");

    const { data: eventsData, isLoading: eventsLoading } = useQuery({
        queryKey: queryKeys.events({
            status: "published",
            per_page: 6,
            ...(searchTerm && { search: searchTerm }),
            ...(selectedVenue &&
                selectedVenue !== "all" && {
                    venue_id: parseInt(selectedVenue),
                }),
        }),
        queryFn: () =>
            eventsApi.getEvents({
                status: "published",
                per_page: 6,
                ...(searchTerm && { search: searchTerm }),
                ...(selectedVenue &&
                    selectedVenue !== "all" && {
                        venue_id: parseInt(selectedVenue),
                    }),
            }),
    });

    const { data: venues } = useQuery({
        queryKey: queryKeys.venues(),
        queryFn: venuesApi.getVenues,
    });

    const events = eventsData?.data || [];

    return (
        <div className="space-y-12">
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-primary/10 via-primary/5 to-transparent py-20">
                <div className="container mx-auto px-4 text-center space-y-8">
                    <div className="space-y-4">
                        <h1 className="text-4xl md:text-6xl font-bold tracking-tight">
                            Discover Amazing{" "}
                            <span className="text-primary">Events</span>
                        </h1>
                        <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
                            Find and book tickets for the best concerts, theater
                            shows, sports events, and more in your area.
                        </p>
                    </div>

                    {/* Search Bar */}
                    <div className="max-w-4xl mx-auto">
                        <Card>
                            <CardContent className="p-6">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="relative">
                                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            placeholder="Search events..."
                                            value={searchTerm}
                                            onChange={(e) =>
                                                setSearchTerm(e.target.value)
                                            }
                                            className="pl-10"
                                        />
                                    </div>

                                    <Select
                                        value={selectedVenue}
                                        onValueChange={setSelectedVenue}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All venues" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">
                                                All venues
                                            </SelectItem>
                                            {venues?.data?.map((venue) => (
                                                <SelectItem
                                                    key={venue.id}
                                                    value={venue.id.toString()}
                                                >
                                                    {venue.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <Button asChild className="w-full">
                                        <Link
                                            to={`/events?${new URLSearchParams({
                                                ...(searchTerm && {
                                                    search: searchTerm,
                                                }),
                                                ...(selectedVenue &&
                                                    selectedVenue !== "all" && {
                                                        venue_id: selectedVenue,
                                                    }),
                                            }).toString()}`}
                                        >
                                            Search Events
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            {/* Upcoming Events */}
            <section className="container mx-auto px-4">
                <div className="space-y-8">
                    <div className="text-center space-y-4">
                        <h2 className="text-3xl font-bold tracking-tight">
                            Upcoming Events
                        </h2>
                        <p className="text-muted-foreground">
                            Don't miss out on these amazing upcoming events
                        </p>
                    </div>

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
                            <p className="text-muted-foreground">
                                Try adjusting your search criteria or check back
                                later.
                            </p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {events.map((event) => {
                                const eventStatus = getEventStatus(event);

                                return (
                                    <Card
                                        key={event.id}
                                        className="overflow-hidden hover:shadow-lg transition-shadow duration-300"
                                    >
                                        <div className="aspect-[4/3] bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center">
                                            <CalendarDays className="h-16 w-16 text-primary/60" />
                                        </div>
                                        <CardContent className="p-6 space-y-4">
                                            <div className="space-y-2">
                                                <div className="flex items-start justify-between">
                                                    <h3 className="font-semibold text-lg line-clamp-2 flex-1">
                                                        {event.name}
                                                    </h3>
                                                    <Badge
                                                        variant={
                                                            eventStatus.variant
                                                        }
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
                                                        {formatDate(
                                                            event.start_date
                                                        )}
                                                    </span>
                                                </div>
                                                {event.venue && (
                                                    <div className="flex items-center gap-2">
                                                        <MapPin className="h-4 w-4 text-muted-foreground" />
                                                        <span>
                                                            {event.venue.name}
                                                        </span>
                                                    </div>
                                                )}
                                                {event.venue && (
                                                    <div className="flex items-center gap-2">
                                                        <Users className="h-4 w-4 text-muted-foreground" />
                                                        <span>
                                                            Capacity:{" "}
                                                            {
                                                                event.venue
                                                                    .capacity
                                                            }
                                                        </span>
                                                    </div>
                                                )}
                                            </div>

                                            <a
                                                href={`/events/${event.id}`}
                                                className="block"
                                            >
                                                <Button className="w-full">
                                                    View Details
                                                </Button>
                                            </a>
                                        </CardContent>
                                    </Card>
                                );
                            })}
                        </div>
                    )}

                    {!eventsLoading && events.length > 0 && (
                        <div className="text-center">
                            <Button variant="outline" size="lg" asChild>
                                <Link to="/events">View All Events</Link>
                            </Button>
                        </div>
                    )}
                </div>
            </section>

            {/* Features Section */}
            <section className="bg-muted/50 py-20">
                <div className="container mx-auto px-4">
                    <div className="text-center space-y-4 mb-12">
                        <h2 className="text-3xl font-bold tracking-tight">
                            Why Choose EventRes?
                        </h2>
                        <p className="text-muted-foreground max-w-2xl mx-auto">
                            We make it easy to discover, book, and manage your
                            event tickets
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div className="text-center space-y-4">
                            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto">
                                <Search className="h-8 w-8 text-primary" />
                            </div>
                            <h3 className="text-xl font-semibold">
                                Easy Discovery
                            </h3>
                            <p className="text-muted-foreground">
                                Find events that match your interests with our
                                powerful search and filtering tools.
                            </p>
                        </div>

                        <div className="text-center space-y-4">
                            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto">
                                <CalendarDays className="h-8 w-8 text-primary" />
                            </div>
                            <h3 className="text-xl font-semibold">
                                Secure Booking
                            </h3>
                            <p className="text-muted-foreground">
                                Book your tickets securely with our reliable
                                payment system and instant confirmation.
                            </p>
                        </div>

                        <div className="text-center space-y-4">
                            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto">
                                <Users className="h-8 w-8 text-primary" />
                            </div>
                            <h3 className="text-xl font-semibold">
                                Manage Tickets
                            </h3>
                            <p className="text-muted-foreground">
                                Keep track of all your tickets in one place and
                                easily transfer or cancel when needed.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
}
