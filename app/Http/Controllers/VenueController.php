<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\Seat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $venues = Venue::orderBy('name')->get();
        return $this->successResponse($venues, 'Venues retrieved successfully');
    }

    public function show(Venue $venue): JsonResponse
    {
        return $this->successResponse($venue, 'Venue retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1|max:50000',
        ]);

        $venue = Venue::create($validated);

        return $this->createdResponse($venue, 'Venue created successfully');
    }

    public function update(Request $request, Venue $venue): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'capacity' => 'required|integer|min:1|max:50000',
        ]);

        $oldCapacity = $venue->capacity;
        $venue->update($validated);
        
        // Auto-sync seats if capacity changed
        if ($oldCapacity != $validated['capacity']) {
            $this->syncSeatsToCapacity($venue, $validated['capacity']);
        }

        return $this->successResponse($venue, 'Venue updated successfully');
    }

    /**
     * Synchronize seat records to match the venue capacity.
     * - If capacity increased: generate additional seats following the seeder pattern
     * - If capacity decreased: try to delete only seats with no tickets or reservation items
     */
    private function syncSeatsToCapacity(Venue $venue, int $targetCapacity): void
    {
        $currentCount = Seat::where('venue_id', $venue->id)->count();

        if ($currentCount === $targetCapacity) {
            return; // Already in sync
        }

        // If capacity decreased, attempt safe deletions first
        $deleted = 0;
        if ($currentCount > $targetCapacity) {
            $toRemove = $currentCount - $targetCapacity;
            $deletable = Seat::where('venue_id', $venue->id)
                ->doesntHave('tickets')
                ->whereDoesntHave('rezervationItems')
                ->orderByDesc('id')
                ->take($toRemove)
                ->get();

            foreach ($deletable as $seat) {
                $seat->delete();
                $deleted++;
            }

            return; // Deletion done, exit
        }

        // Capacity increased → create additional seats following the seeder pattern
        $need = $targetCapacity - $currentCount;
        $created = 0;
        $sections = range('A', 'K'); // A..K

        foreach ($sections as $section) {
            for ($row = 1; $row <= 10; $row++) {
                for ($number = 1; $number <= 20; $number++) {
                    if (($currentCount + $created) >= $targetCapacity) {
                        break 3;
                    }

                    $exists = Seat::where('venue_id', $venue->id)
                        ->where('section', $section)
                        ->where('row', $row)
                        ->where('number', $number)
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    $basePrice = 1000;
                    $sectionIndex = array_search($section, $sections); // 0-based
                    $price = $basePrice - ($sectionIndex * 10) - ($row * 10);

                    Seat::create([
                        'venue_id' => $venue->id,
                        'section' => $section,
                        'row' => $row,
                        'number' => $number,
                        'price' => $price,
                    ]);
                    $created++;
                }
            }
        }

        // Auto-sync complete
    }

    public function destroy(Venue $venue): JsonResponse
    {
        if ($venue->events()->exists()) {
            return $this->errorResponse('Cannot delete venue with associated events', 409);
        }

        $venue->delete();

        return $this->successResponse(null, 'Venue deleted successfully');
    }
}
