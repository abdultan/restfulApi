import React from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "../../components/ui/card";
import { Button } from "../../components/ui/button";
import { Badge } from "../../components/ui/badge";
import { eventsApi } from "../../api/events";
import { venuesApi } from "../../api/venues";
import { queryKeys } from "../../lib/queryClient";
import {
    Calendar,
    MapPin,
    Users,
    Ticket,
    TrendingUp,
    Plus,
    Settings,
    BarChart3,
} from "lucide-react";
import { formatDate } from "../../lib/utils";

export function AdminDashboard() {
    const { data: eventsData } = useQuery({
        queryKey: queryKeys.events({ per_page: 1000 }),
        queryFn: () => eventsApi.getEvents({ per_page: 1000 }),
    });

    const { data: venues } = useQuery({
        queryKey: queryKeys.venues(),
        queryFn: venuesApi.getVenues,
    });

    const events = eventsData?.data || [];
    const activeEvents = events.filter((e) => e.status === "active");
    const totalCapacity =
        venues?.data?.reduce((sum, venue) => sum + venue.capacity, 0) || 0;

    const stats = [
        {
            title: "Total Events",
            value: events.length,
            icon: Calendar,
            color: "text-blue-500",
            bgColor: "bg-blue-500/10",
        },
        {
            title: "Active Events",
            value: activeEvents.length,
            icon: TrendingUp,
            color: "text-green-500",
            bgColor: "bg-green-500/10",
        },
        {
            title: "Total Venues",
            value: venues?.data?.length || 0,
            icon: MapPin,
            color: "text-purple-500",
            bgColor: "bg-purple-500/10",
        },
        {
            title: "Total Capacity",
            value: totalCapacity.toLocaleString(),
            icon: Users,
            color: "text-orange-500",
            bgColor: "bg-orange-500/10",
        },
    ];

    const quickActions = [
        {
            title: "Manage Events",
            description: "View and edit existing events",
            icon: Calendar,
            href: "/admin/events",
            color: "text-green-500",
        },
        {
            title: "Manage Venues",
            description: "Add and edit venue information",
            icon: MapPin,
            href: "/admin/venues",
            color: "text-purple-500",
        },
        {
            title: "View All Events",
            description: "Browse events from user perspective",
            icon: Ticket,
            href: "/events",
            color: "text-blue-500",
        },
        {
            title: "Back to Home",
            description: "Return to main application",
            icon: Settings,
            href: "/",
            color: "text-orange-500",
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 space-y-8">
            {/* Header */}
            <div className="space-y-4">
                <h1 className="text-3xl font-bold tracking-tight">
                    Admin Dashboard
                </h1>
                <p className="text-muted-foreground">
                    Manage events, venues, and monitor system performance
                </p>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {stats.map((stat) => (
                    <Card
                        key={stat.title}
                        className="hover:shadow-md transition-shadow"
                    >
                        <CardContent className="p-6">
                            <div className="flex items-center space-x-4">
                                <div
                                    className={`p-3 rounded-full ${stat.bgColor}`}
                                >
                                    <stat.icon
                                        className={`h-6 w-6 ${stat.color}`}
                                    />
                                </div>
                                <div>
                                    <div className="text-2xl font-bold">
                                        {stat.value}
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {stat.title}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Settings className="h-5 w-5" />
                        Quick Actions
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {quickActions.map((action) => (
                            <Card
                                key={action.title}
                                className="hover:shadow-md transition-shadow"
                            >
                                <CardContent className="p-6">
                                    <div className="flex items-start space-x-4">
                                        <div className="p-2 rounded-lg bg-muted">
                                            <action.icon
                                                className={`h-5 w-5 ${action.color}`}
                                            />
                                        </div>
                                        <div className="flex-1 space-y-2">
                                            <h3 className="font-semibold">
                                                {action.title}
                                            </h3>
                                            <p className="text-sm text-muted-foreground">
                                                {action.description}
                                            </p>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link to={action.href}>
                                                    Get Started
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </CardContent>
            </Card>

            {/* Recent Events */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center justify-between">
                        <span className="flex items-center gap-2">
                            <Calendar className="h-5 w-5" />
                            Recent Events
                        </span>
                        <Button variant="outline" size="sm" asChild>
                            <Link to="/admin/events">View All</Link>
                        </Button>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {events.length === 0 ? (
                        <div className="text-center py-8">
                            <Calendar className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                            <h3 className="text-lg font-medium mb-2">
                                No events yet
                            </h3>
                            <p className="text-muted-foreground mb-4">
                                Create your first event to get started
                            </p>
                            <Button asChild>
                                <Link to="/admin/events/create">
                                    <Plus className="h-4 w-4 mr-2" />
                                    Create Event
                                </Link>
                            </Button>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {events.slice(0, 5).map((event) => (
                                <div
                                    key={event.id}
                                    className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <div className="space-y-1">
                                        <div className="font-medium">
                                            {event.name}
                                        </div>
                                        <div className="text-sm text-muted-foreground flex items-center gap-4">
                                            <span className="flex items-center gap-1">
                                                <Calendar className="h-3 w-3" />
                                                {formatDate(event.start_date)}
                                            </span>
                                            {event.venue && (
                                                <span className="flex items-center gap-1">
                                                    <MapPin className="h-3 w-3" />
                                                    {event.venue.name}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge
                                            variant={
                                                event.status === "active"
                                                    ? "success"
                                                    : "secondary"
                                            }
                                        >
                                            {event.status}
                                        </Badge>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <Link to="/admin/events">
                                                Manage
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Analytics Placeholder */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <BarChart3 className="h-5 w-5" />
                        Analytics Overview
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-center py-12">
                        <BarChart3 className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                        <h3 className="text-lg font-medium mb-2">
                            Analytics Coming Soon
                        </h3>
                        <p className="text-muted-foreground">
                            Detailed analytics and reporting features will be
                            available in a future update.
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
