<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\FarmerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index()
    {
        $announcements = Announcement::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create()
    {
        $municipalities = Crop::distinct()->orderBy('municipality')->pluck('municipality');
        
        return view('admin.announcements.create', compact('municipalities'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'priority' => 'required|in:low,normal,high,urgent',
            'target_audience' => 'required|in:all,farmers,admins',
            'municipality' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');
        $validated['municipality'] = !empty($validated['municipality']) ? $validated['municipality'] : null;

        // If no published_at date, publish immediately
        if (empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $announcement = Announcement::create($validated);

        if ($validated['is_active'] && in_array($validated['target_audience'], ['all', 'farmers'])) {
            $this->dispatchFarmerNotifications($announcement);
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement)
    {
        $municipalities = Crop::distinct()->orderBy('municipality')->pluck('municipality');
        
        return view('admin.announcements.edit', compact('announcement', 'municipalities'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
            'priority' => 'required|in:low,normal,high,urgent',
            'target_audience' => 'required|in:all,farmers,admins',
            'municipality' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['municipality'] = !empty($validated['municipality']) ? $validated['municipality'] : null;

        $wasActive = $announcement->is_active;
        $announcement->update($validated);

        // Notify farmers when announcement is newly activated
        if ($validated['is_active'] && !$wasActive && in_array($validated['target_audience'], ['all', 'farmers'])) {
            $this->dispatchFarmerNotifications($announcement);
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Toggle announcement active status.
     */
    public function toggleStatus(Announcement $announcement)
    {
        $wasActive = $announcement->is_active;
        $announcement->update(['is_active' => !$announcement->is_active]);

        // When activating, send farmer notifications
        if (!$wasActive && $announcement->is_active && in_array($announcement->target_audience, ['all', 'farmers'])) {
            $this->dispatchFarmerNotifications($announcement);
        }

        $status = $announcement->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Announcement {$status} successfully!");
    }

    /**
     * Get recent announcements as admin notifications (JSON API).
     */
    public function getAdminNotifications()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(function ($a) {
                return [
                    'id'         => $a->id,
                    'title'      => $a->title,
                    'message'    => Str::limit(strip_tags($a->content), 100),
                    'priority'   => $a->priority,
                    'is_active'  => $a->is_active,
                    'time_ago'   => $a->created_at->diffForHumans(),
                    'created_at' => $a->created_at->format('Y-m-d H:i:s'),
                    'link'       => '/admin/announcements/' . $a->id,
                ];
            });

        // Unread = announcements created in last 24 hours that are active
        $unreadCount = Announcement::where('is_active', true)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return response()->json([
            'success'      => true,
            'notifications' => $announcements,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Mark an admin announcement notification as read (no-op, just returns success).
     */
    public function markAdminNotificationRead(Announcement $announcement)
    {
        return response()->json(['success' => true]);
    }

    /**
     * Create FarmerNotification records for all matching farmers.
     */
    private function dispatchFarmerNotifications(Announcement $announcement): void
    {
        $iconColor = match ($announcement->priority) {
            'urgent' => 'red',
            'high'   => 'orange',
            'normal' => 'blue',
            default  => 'gray',
        };

        $query = Farmer::query();

        if (!empty($announcement->municipality)) {
            $query->where('municipality', $announcement->municipality);
        }

        $farmerIds = $query->pluck('id');

        // Avoid duplicate notifications for the same announcement per farmer
        $existing = FarmerNotification::where('type', FarmerNotification::TYPE_ANNOUNCEMENT)
            ->whereIn('farmer_id', $farmerIds)
            ->get()
            ->filter(function ($n) use ($announcement) {
                $data = is_array($n->data) ? $n->data : json_decode($n->data, true);
                return isset($data['announcement_id']) && $data['announcement_id'] === $announcement->id;
            })
            ->pluck('farmer_id')
            ->flip();

        $now = now();
        $inserts = [];

        foreach ($farmerIds as $farmerId) {
            if ($existing->has($farmerId)) {
                continue;
            }

            $inserts[] = [
                'farmer_id'  => $farmerId,
                'type'       => FarmerNotification::TYPE_ANNOUNCEMENT,
                'title'      => $announcement->title,
                'message'    => Str::limit(strip_tags($announcement->content), 120),
                'icon'       => 'megaphone',
                'icon_color' => $iconColor,
                'link'       => route('farmers.dashboard'),
                'data'       => json_encode(['announcement_id' => $announcement->id]),
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($inserts)) {
            FarmerNotification::insert($inserts);
        }
    }
}
