import React, { useState, useMemo } from "react";
import { Button } from "../ui/button";
import { Card, CardContent } from "../ui/card";
import { Badge } from "../ui/badge";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "../ui/tooltip";
import { Seat } from "../../types/api";
import {
    getSeatStatusColor,
    getSeatStatusIcon,
    formatCurrency,
} from "../../lib/utils";
import { SeatLegend } from "./SeatLegend";
import { cn } from "../../lib/utils";

interface SeatMapProps {
    seats: Seat[];
    onSeatSelect: (seatIds: number[]) => void;
    selectedSeats: number[];
    disabled?: boolean;
    capacity?: number; // Venue capacity (optional)
}

interface SeatGridProps {
    seats: Seat[];
    selectedSeats: number[];
    onSeatClick: (seat: Seat) => void;
    disabled?: boolean;
}

function SeatGrid({
    seats,
    selectedSeats,
    onSeatClick,
    disabled,
}: SeatGridProps) {
    // Group seats by section+row to avoid mixing different sections in the same visual row
    const seatsByRow = useMemo(() => {
        return seats.reduce((acc, seat) => {
            const rowKey = `${seat.section}-${seat.row}`;
            if (!acc[rowKey]) {
                acc[rowKey] = [];
            }
            acc[rowKey].push(seat);
            return acc;
        }, {} as Record<string, Seat[]>);
    }, [seats]);

    // Sort rows by section then row
    const sortedRows = Object.keys(seatsByRow).sort((a, b) => {
        const [secA, rowA] = a.split("-");
        const [secB, rowB] = b.split("-");
        if (secA === secB) return Number(rowA) - Number(rowB);
        return secA.localeCompare(secB);
    });

    return (
        <div className="space-y-2">
            {/* Stage */}
            <div className="text-center mb-6">
                <div className="inline-block bg-gradient-to-r from-primary/20 to-primary/30 px-8 py-2 rounded-lg">
                    <span className="text-sm font-medium">STAGE</span>
                </div>
            </div>

            {/* Seats */}
            <TooltipProvider>
                <div className="space-y-3 max-h-[600px] overflow-auto">
                    {sortedRows.map((row) => {
                        const rowSeats = seatsByRow[row].sort(
                            (a, b) => parseInt(a.number) - parseInt(b.number)
                        );
                        const [section, rowNum] = row.split("-");

                        return (
                            <div
                                key={row}
                                className="flex items-center justify-center space-x-1"
                            >
                                {/* Row Label */}
                                <div className="w-8 text-center text-sm font-medium text-muted-foreground">
                                    {section}
                                    {rowNum}
                                </div>

                                {/* Seats in Row */}
                                <div className="flex space-x-1">
                                    {rowSeats.map((seat) => {
                                        const isSelected =
                                            selectedSeats.includes(seat.id);
                                        const isAvailable =
                                            seat.status === "available";
                                        const canClick =
                                            !disabled && isAvailable;

                                        return (
                                            <Tooltip key={seat.id}>
                                                <TooltipTrigger asChild>
                                                    <button
                                                        onClick={() =>
                                                            canClick &&
                                                            onSeatClick(seat)
                                                        }
                                                        disabled={!canClick}
                                                        className={cn(
                                                            "w-8 h-8 rounded text-xs font-medium border transition-all duration-200 hover:scale-105",
                                                            isSelected &&
                                                                isAvailable &&
                                                                "ring-2 ring-primary ring-offset-2",
                                                            getSeatStatusColor(
                                                                seat.status
                                                            ),
                                                            !canClick &&
                                                                "cursor-not-allowed opacity-75"
                                                        )}
                                                    >
                                                        {seat.number}
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    <div className="text-center">
                                                        <div className="font-medium">
                                                            {seat.label}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            {formatCurrency(
                                                                seat.price
                                                            )}
                                                        </div>
                                                        <div className="text-xs">
                                                            {seat.status ===
                                                                "available" &&
                                                                "Available"}
                                                            {seat.status ===
                                                                "sold" &&
                                                                "Sold"}
                                                            {seat.status ===
                                                                "reserved" &&
                                                                "Reserved"}
                                                        </div>
                                                    </div>
                                                </TooltipContent>
                                            </Tooltip>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </TooltipProvider>
        </div>
    );
}

export function SeatMap({
    seats,
    onSeatSelect,
    selectedSeats,
    disabled = false,
    capacity,
}: SeatMapProps) {
    const handleSeatClick = (seat: Seat) => {
        if (seat.status !== "available" || disabled) return;

        const isSelected = selectedSeats.includes(seat.id);
        let newSelection: number[];

        if (isSelected) {
            // Remove seat from selection
            newSelection = selectedSeats.filter((id) => id !== seat.id);
        } else {
            // Add seat to selection
            newSelection = [...selectedSeats, seat.id];
        }

        onSeatSelect(newSelection);
    };

    const selectedSeatDetails = useMemo(() => {
        return seats.filter((seat) => selectedSeats.includes(seat.id));
    }, [seats, selectedSeats]);

    const totalPrice = selectedSeatDetails.reduce(
        (sum, seat) => sum + seat.price,
        0
    );

    const seatCounts = useMemo(() => {
        return seats.reduce((acc, seat) => {
            acc[seat.status] = (acc[seat.status] || 0) + 1;
            return acc;
        }, {} as Record<string, number>);
    }, [seats]);

    if (seats.length === 0) {
        return (
            <Card>
                <CardContent className="p-8 text-center">
                    <p className="text-muted-foreground">
                        No seats available for this event.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Seat Map */}
            <div className="lg:col-span-2">
                <Card>
                    <CardContent className="p-6">
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <h3 className="text-lg font-semibold">
                                    Select Your Seats
                                </h3>
                                <SeatLegend />
                            </div>

                            <SeatGrid
                                seats={seats}
                                selectedSeats={selectedSeats}
                                onSeatClick={handleSeatClick}
                                disabled={disabled}
                            />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Selection Summary */}
            <div className="space-y-4">
                {/* Summary Card */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="font-semibold mb-4">
                            Selection Summary
                        </h3>

                        {selectedSeats.length === 0 ? (
                            <p className="text-muted-foreground text-center py-4">
                                Select seats to see summary
                            </p>
                        ) : (
                            <div className="space-y-4">
                                {/* Selected Seats */}
                                <div className="space-y-2">
                                    <div className="text-sm font-medium">
                                        Selected Seats:
                                    </div>
                                    <div className="flex flex-wrap gap-1">
                                        {selectedSeatDetails.map((seat) => (
                                            <Badge
                                                key={seat.id}
                                                variant="secondary"
                                                className="text-xs"
                                            >
                                                {seat.label}
                                            </Badge>
                                        ))}
                                    </div>
                                </div>

                                {/* Price Breakdown */}
                                <div className="space-y-1 text-sm">
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

                                {/* Total */}
                                <div className="border-t pt-2">
                                    <div className="flex justify-between font-semibold">
                                        <span>
                                            Total ({selectedSeats.length} seats)
                                        </span>
                                        <span>
                                            {formatCurrency(totalPrice)}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Availability Summary */}
                <Card>
                    <CardContent className="p-6">
                        <h3 className="font-semibold mb-4">Availability</h3>
                        <div className="space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="flex items-center gap-2">
                                    <span>🟢</span>
                                    Available
                                </span>
                                <span>{seatCounts.available || 0}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="flex items-center gap-2">
                                    <span>🔴</span>
                                    Sold
                                </span>
                                <span>{seatCounts.sold || 0}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="flex items-center gap-2">
                                    <span>🟡</span>
                                    Reserved
                                </span>
                                <span>{seatCounts.reserved || 0}</span>
                            </div>
                            <div className="border-t pt-2 space-y-1 font-medium">
                                <div className="flex justify-between">
                                    <span>Total seats</span>
                                    <span>{seats.length}</span>
                                </div>
                                {typeof capacity === "number" && (
                                    <div className="flex justify-between text-muted-foreground">
                                        <span>Capacity</span>
                                        <span>{capacity}</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
