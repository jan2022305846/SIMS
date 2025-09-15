<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    /**
     * Display the main help index
     */
    public function index()
    {
        $user = Auth::user();
        
        $helpSections = $this->getHelpSections($user);
        
        return view('help.index', compact('helpSections'));
    }

    /**
     * Show specific help topic
     */
    public function show($topic)
    {
        $user = Auth::user();
        $helpContent = $this->getHelpContent($topic, $user);
        
        if (!$helpContent) {
            abort(404, 'Help topic not found.');
        }
        
        return view('help.show', compact('helpContent', 'topic'));
    }

    /**
     * Search help topics
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $user = Auth::user();
        
        $results = $this->searchHelpTopics($query, $user);
        
        return response()->json([
            'query' => $query,
            'results' => $results,
            'count' => count($results)
        ]);
    }

    /**
     * Get help sections based on user role
     */
    private function getHelpSections($user): array
    {
        $sections = [
            'getting-started' => [
                'title' => 'Getting Started',
                'description' => 'Basic navigation and system overview',
                'icon' => 'fas fa-play-circle',
                'topics' => [
                    'dashboard-overview' => 'Understanding the Dashboard',
                    'navigation' => 'System Navigation',
                    'user-profile' => 'Managing Your Profile'
                ]
            ]
        ];

        if (in_array($user->role, ['admin', 'office_head'])) {
            $sections['inventory-management'] = [
                'title' => 'Inventory Management',
                'description' => 'Managing items, stock, and categories',
                'icon' => 'fas fa-boxes',
                'topics' => [
                    'add-item' => 'How to Add New Items',
                    'edit-item' => 'Editing Item Details',
                    'stock-management' => 'Managing Stock Levels',
                    'categories' => 'Working with Categories',
                    'qr-codes' => 'Using QR Code System',
                    'bulk-operations' => 'Bulk Item Operations'
                ]
            ];

            $sections['request-processing'] = [
                'title' => 'Request Processing',
                'description' => 'Handling supply requests and approvals',
                'icon' => 'fas fa-clipboard-check',
                'topics' => [
                    'process-requests' => 'How to Process Requests',
                    'approval-workflow' => 'Understanding Approval Workflow',
                    'acknowledgments' => 'Digital Acknowledgment System',
                    'request-reports' => 'Request Reports and Analytics'
                ]
            ];

            $sections['reports-analytics'] = [
                'title' => 'Reports & Analytics',
                'description' => 'Generating reports and viewing analytics',
                'icon' => 'fas fa-chart-bar',
                'topics' => [
                    'dashboard-analytics' => 'Dashboard Analytics',
                    'inventory-reports' => 'Inventory Reports',
                    'activity-logs' => 'Activity Logs and Audit Trail',
                    'export-data' => 'Exporting Data'
                ]
            ];

            $sections['system-administration'] = [
                'title' => 'System Administration',
                'description' => 'User management and system settings',
                'icon' => 'fas fa-cogs',
                'topics' => [
                    'user-management' => 'Managing Users',
                    'system-settings' => 'System Configuration',
                    'backup-restore' => 'Backup and Restore',
                    'troubleshooting' => 'Common Issues and Solutions'
                ]
            ];
        }

        if ($user->role === 'faculty' || in_array($user->role, ['admin', 'office_head'])) {
            $sections['making-requests'] = [
                'title' => 'Making Requests',
                'description' => 'How to submit and track supply requests',
                'icon' => 'fas fa-hand-paper',
                'topics' => [
                    'create-request' => 'How to Create a Request',
                    'track-request' => 'Tracking Your Requests',
                    'request-status' => 'Understanding Request Status',
                    'acknowledgment-process' => 'Digital Acknowledgment Process'
                ]
            ];

            $sections['browsing-inventory'] = [
                'title' => 'Browsing Inventory',
                'description' => 'Finding and viewing available items',
                'icon' => 'fas fa-search',
                'topics' => [
                    'browse-items' => 'Browsing Available Items',
                    'search-filters' => 'Using Search and Filters',
                    'item-details' => 'Understanding Item Information'
                ]
            ];
        }

        return $sections;
    }

    /**
     * Get detailed help content for a specific topic
     */
    private function getHelpContent($topic, $user): ?array
    {
        $helpTopics = [
            'dashboard-overview' => [
                'title' => 'Understanding the Dashboard',
                'description' => 'Learn how to navigate and use the main dashboard',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The dashboard is your central hub for managing supply office operations. It provides real-time insights and quick access to key features.'
                    ],
                    [
                        'type' => 'image',
                        'src' => '/images/help/dashboard-overview.png',
                        'alt' => 'Dashboard Overview'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Dashboard Components:',
                        'steps' => [
                            'Statistics Cards - View key metrics at a glance',
                            'Quick Actions - Perform common tasks quickly',
                            'Recent Activities - See latest system activities',
                            'Alerts - Important notifications and warnings'
                        ]
                    ]
                ],
                'tags' => ['dashboard', 'overview', 'navigation'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'add-item' => [
                'title' => 'How to Add New Items',
                'description' => 'Step-by-step guide to adding items to the inventory',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Adding new items to the inventory is a crucial task for maintaining accurate stock levels and ensuring availability for requests.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'To add a new item:',
                        'steps' => [
                            'Navigate to "Items" in the main menu',
                            'Click the "Add New Item" button',
                            'Fill in all required fields (marked with *)',
                            'Upload an item image if available',
                            'Set initial stock quantity and minimum stock level',
                            'Assign the item to appropriate category',
                            'Add any additional details or notes',
                            'Click "Save Item" to complete'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Always double-check the unit price and initial stock quantity before saving.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Pro Tips:',
                        'tips' => [
                            'Use clear, descriptive names for easy searching',
                            'Set realistic minimum stock levels to avoid shortages',
                            'Include supplier information for easy reordering',
                            'Take advantage of bulk import for multiple items'
                        ]
                    ]
                ],
                'tags' => ['items', 'inventory', 'add', 'create'],
                'roles' => ['admin', 'office_head']
            ],

            'create-request' => [
                'title' => 'How to Create a Request',
                'description' => 'Complete guide to submitting supply requests',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The request system allows you to formally request supplies from the office. Follow these steps to create a successful request.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Creating a request:',
                        'steps' => [
                            'Go to "Requests" → "New Request"',
                            'Select items from the available inventory',
                            'Specify quantities needed for each item',
                            'Provide a clear purpose for the request',
                            'Set priority level (high, medium, low)',
                            'Add any additional notes or requirements',
                            'Review your request before submitting',
                            'Click "Submit Request"'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Your request will be reviewed by the office staff. You\'ll receive notifications about status updates.'
                    ]
                ],
                'tags' => ['request', 'create', 'submit', 'faculty'],
                'roles' => ['faculty', 'admin', 'office_head']
            ],

            'process-requests' => [
                'title' => 'How to Process Requests',
                'description' => 'Guide for reviewing and approving supply requests',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Processing requests efficiently is key to maintaining good service for faculty members.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Request processing workflow:',
                        'steps' => [
                            'Review pending requests in the dashboard',
                            'Click on a request to view details',
                            'Verify item availability and quantities',
                            'Check if the request meets approval criteria',
                            'Either approve, reject, or request modifications',
                            'Add comments explaining your decision',
                            'Notify the requester of status change'
                        ]
                    ]
                ],
                'tags' => ['requests', 'process', 'approve', 'admin'],
                'roles' => ['admin', 'office_head']
            ],

            'acknowledgment-process' => [
                'title' => 'Digital Acknowledgment Process',
                'description' => 'Understanding the digital signature and acknowledgment system',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The digital acknowledgment system provides a secure, verifiable way to confirm receipt of requested items.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Acknowledgment process:',
                        'steps' => [
                            'Receive notification that items are ready for pickup',
                            'Visit the supply office to collect items',
                            'Verify all items and quantities are correct',
                            'Provide digital signature using touch/mouse',
                            'Optional: Take photo evidence of received items',
                            'Submit acknowledgment form',
                            'Receive digital receipt with verification hash'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'The digital receipt includes GPS coordinates and timestamp for security verification.'
                    ]
                ],
                'tags' => ['acknowledgment', 'digital signature', 'receipt'],
                'roles' => ['faculty', 'admin', 'office_head']
            ],

            'qr-codes' => [
                'title' => 'Using QR Code System',
                'description' => 'How to generate and scan QR codes for items',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'QR codes provide quick access to item information and enable easy tracking.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Using QR codes:',
                        'steps' => [
                            'Generate QR codes from item detail pages',
                            'Print and attach codes to physical items',
                            'Use dashboard scanner or mobile app to scan',
                            'Quickly access item details and history',
                            'Update stock levels through scanning'
                        ]
                    ]
                ],
                'tags' => ['qr', 'scan', 'tracking'],
                'roles' => ['admin', 'office_head']
            ]
        ];

        // Check if user has access to this topic
        if (!isset($helpTopics[$topic])) {
            return null;
        }

        $content = $helpTopics[$topic];
        if (!in_array($user->role, $content['roles'])) {
            return null;
        }

        return $content;
    }

    /**
     * Search help topics
     */
    private function searchHelpTopics($query, $user): array
    {
        if (empty($query)) {
            return [];
        }

        $results = [];
        $sections = $this->getHelpSections($user);

        foreach ($sections as $sectionKey => $section) {
            foreach ($section['topics'] as $topicKey => $topicTitle) {
                $content = $this->getHelpContent($topicKey, $user);
                if ($content) {
                    $searchText = strtolower($content['title'] . ' ' . $content['description'] . ' ' . implode(' ', $content['tags']));
                    if (strpos($searchText, strtolower($query)) !== false) {
                        $results[] = [
                            'topic' => $topicKey,
                            'title' => $content['title'],
                            'description' => $content['description'],
                            'section' => $section['title'],
                            'url' => route('help.show', $topicKey)
                        ];
                    }
                }
            }
        }

        return $results;
    }
}