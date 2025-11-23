<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            ],
            'feedback' => [
                'title' => 'Feedback & Support',
                'description' => 'Report bugs and provide feedback',
                'icon' => 'fas fa-comments',
                'topics' => [
                    'report-bug' => 'Report a Bug',
                    'feature-request' => 'Request a Feature'
                ]
            ],
            'updates' => [
                'title' => 'What\'s New',
                'description' => 'Recent updates and improvements',
                'icon' => 'fas fa-star',
                'topics' => [
                    'latest-updates' => 'Latest Updates',
                    'version-history' => 'Version History'
                ]
            ]
        ];

        if (in_array($user->role, ['admin', 'office_head'])) {
            $sections['inventory-management'] = [
                'title' => 'Inventory Management',
                'description' => 'Managing items, stock levels, and categories',
                'icon' => 'fas fa-boxes',
                'topics' => [
                    'add-item' => 'How to Add New Items',
                    'edit-item' => 'Editing Item Details',
                    'stock-management' => 'Managing Stock Levels',
                    'categories' => 'Working with Categories'
                ]
            ];

            $sections['request-management'] = [
                'title' => 'Request Management',
                'description' => 'Handling supply requests and approvals',
                'icon' => 'fas fa-clipboard-check',
                'topics' => [
                    'view-requests' => 'Viewing and Managing Requests',
                    'process-requests' => 'How to Process Requests',
                    'fulfill-requests' => 'Fulfilling and Claiming Requests'
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
                    'track-request' => 'Tracking Your Requests'
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
                        'type' => 'steps',
                        'title' => 'Dashboard Components:',
                        'steps' => [
                            'Statistics Cards - View key metrics at a glance (total items, users, pending requests)',
                            'Low Stock Alerts - Items that need restocking',
                            'Recent Activities - Latest system activities and changes',
                            'Pending Requests - Requests awaiting your approval (admin only)',
                            'Quick Actions - Fast access to common tasks',
                            'System Health - Overall system status and performance'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'The dashboard adapts based on your role - administrators see system-wide statistics while faculty members see their personal request information.'
                    ]
                ],
                'tags' => ['dashboard', 'overview', 'navigation'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'navigation' => [
                'title' => 'System Navigation',
                'description' => 'Learn how to navigate through the Supply Office system',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The Supply Office system is designed with an intuitive navigation structure to help you find what you need quickly and efficiently.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Main Navigation Areas:',
                        'steps' => [
                            'Dashboard - Your central hub with overview and quick actions',
                            'Items - Browse, add, edit, and manage inventory items',
                            'Requests - Create and track supply requests (faculty) or manage all requests (admin)',
                            'Reports - View analytics and generate reports (admin only)',
                            'Users - Manage system users (admin only)',
                            'Categories - Organize items by categories (admin only)',
                            'Activity Logs - Monitor system activities (admin only)',
                            'Help - Access documentation and support'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'The navigation menu adapts based on your role - faculty members see different options than administrators.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Navigation Tips:',
                        'tips' => [
                            'Use the search bar in the top navigation for quick access',
                            'Bookmark frequently used pages for faster access',
                            'The breadcrumb navigation shows your current location',
                            'Quick action buttons are available on the dashboard'
                        ]
                    ]
                ],
                'tags' => ['navigation', 'menu', 'interface'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'user-profile' => [
                'title' => 'Managing Your Profile',
                'description' => 'How to update your profile information and preferences',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Your user profile contains important information and settings that help personalize your experience with the Supply Office system.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Updating Your Profile:',
                        'steps' => [
                            'Click your name in the top-right corner',
                            'Select "Profile" from the dropdown menu',
                            'Update your personal information as needed',
                            'Change your password if required',
                            'Save your changes'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Keep your contact information current so you can receive important notifications about your requests.'
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Profile changes are logged for security purposes. Administrators can view activity logs to monitor system usage.'
                    ]
                ],
                'tags' => ['profile', 'settings', 'account'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'report-bug' => [
                'title' => 'Report a Bug',
                'description' => 'How to report bugs and technical issues',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Found a bug or experiencing technical issues? Help us improve the system by reporting it with detailed information.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Reporting a Bug:',
                        'steps' => [
                            'Click the "Report Bug" button below',
                            'Describe the issue in detail',
                            'Include steps to reproduce the problem',
                            'Attach screenshots if possible',
                            'Specify your browser and device information',
                            'Submit the report'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Your bug reports are sent directly to our development team at abuabujanny99@gmail.com for prompt resolution.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Helpful Information to Include:',
                        'tips' => [
                            'What were you trying to do when the issue occurred?',
                            'What did you expect to happen vs. what actually happened?',
                            'Include any error messages you received',
                            'Mention if this is a new issue or has happened before',
                            'Note your browser type and version'
                        ]
                    ]
                ],
                'tags' => ['bug', 'report', 'feedback', 'support'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'feature-request' => [
                'title' => 'Request a Feature',
                'description' => 'Suggest new features and improvements',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Have an idea to improve the Supply Office system? We welcome feature requests and suggestions from our users.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Submitting a Feature Request:',
                        'steps' => [
                            'Click the "Request Feature" button below',
                            'Describe the feature you would like to see',
                            'Explain how it would benefit your workflow',
                            'Include any mockups or examples if available',
                            'Specify priority level (nice-to-have, important, critical)',
                            'Submit your request'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Feature requests are reviewed by our development team. Popular and high-impact suggestions are prioritized for implementation.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'What Makes a Good Feature Request:',
                        'tips' => [
                            'Be specific about what you want and why',
                            'Consider how it fits into existing workflows',
                            'Think about impact on different user roles',
                            'Include examples from similar systems you\'ve used',
                            'Consider the technical feasibility'
                        ]
                    ]
                ],
                'tags' => ['feature', 'request', 'improvement', 'suggestion'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'latest-updates' => [
                'title' => 'Latest Updates',
                'description' => 'Recent improvements and new features',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Stay up-to-date with the latest improvements and new features in the Supply Office system.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.5.0 - November 2025',
                        'content' => 'Enhanced security features, improved QR code verification, and streamlined request processing.'
                    ],
                    [
                        'type' => 'list',
                        'title' => 'New Features:',
                        'items' => [
                            'Secure QR code verification for claim slips with cryptographic hashing',
                            'Bulk request processing for multiple items',
                            'Enhanced dashboard analytics and reporting',
                            'Improved mobile responsiveness',
                            'Advanced search and filtering capabilities'
                        ]
                    ],
                    [
                        'type' => 'list',
                        'title' => 'Bug Fixes:',
                        'items' => [
                            'Fixed claim slip printing issues',
                            'Resolved QR code scanning inconsistencies',
                            'Improved request status notifications',
                            'Fixed stock level calculation errors',
                            'Enhanced error handling for failed operations'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'For a complete changelog, visit our GitHub repository or contact the development team.'
                    ]
                ],
                'tags' => ['updates', 'changelog', 'features', 'fixes'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'version-history' => [
                'title' => 'Version History',
                'description' => 'Complete history of system updates and improvements',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Track the evolution of the Supply Office system through our version history.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.5.0 (Current) - November 23, 2025',
                        'content' => 'Security enhancements, bulk request support, and performance improvements.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.4.0 - October 2025',
                        'content' => 'QR code integration, enhanced reporting, and user interface improvements.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.3.0 - September 2025',
                        'content' => 'Request workflow optimization, inventory management enhancements.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.2.0 - August 2025',
                        'content' => 'Multi-role user system, advanced analytics, and notification system.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Version 2.1.0 - July 2025',
                        'content' => 'Initial public release with core supply request functionality.'
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Regular updates are released to improve functionality, security, and user experience. Check back regularly for new features.'
                    ]
                ],
                'tags' => ['version', 'history', 'changelog', 'updates'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'item-types' => [
                'title' => 'Understanding Item Types (Consumable vs Non-Consumable)',
                'description' => 'Learn the difference between consumable and non-consumable items',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The Supply Office system manages two types of items: consumable and non-consumable. Understanding the difference is crucial for proper inventory management.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Consumable Items:',
                        'content' => 'Items that are used up or depleted during use, such as paper, pens, cleaning supplies, or office consumables. These items have stock quantities that decrease when fulfilled.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Non-Consumable Items:',
                        'content' => 'Items that are not depleted during use, such as equipment, furniture, or durable goods. These items can be assigned to users and tracked individually.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Key Differences:',
                        'steps' => [
                            'Stock Tracking: Consumables track quantity, non-consumables track individual items',
                            'Assignment: Non-consumables can be assigned to specific users, consumables cannot',
                            'Fulfillment: Consumables reduce stock when claimed, non-consumables remain in inventory',
                            'Location: Non-consumables have specific storage locations, consumables have general locations',
                            'Condition: Non-consumables track condition (New, Good, Fair, Needs Repair)'
                        ]
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Management Tips:',
                        'tips' => [
                            'Use consumables for items bought in bulk and used over time',
                            'Use non-consumables for valuable or trackable individual items',
                            'Regularly update non-consumable conditions and locations',
                            'Set appropriate minimum stock levels for consumables'
                        ]
                    ]
                ],
                'tags' => ['items', 'consumable', 'non-consumable', 'inventory', 'types'],
                'roles' => ['admin', 'office_head']
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
                            'Select item type: Consumable or Non-Consumable',
                            'Fill in all required fields (marked with *)',
                            'For non-consumables: specify location and condition',
                            'Set initial stock quantity and minimum stock level',
                            'Assign the item to appropriate category',
                            'Add any additional details or notes',
                            'Click "Save Item" to complete'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Always double-check the item type selection and initial stock quantity before saving.'
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

            'item-assignment' => [
                'title' => 'Assigning Non-Consumable Items',
                'description' => 'How to assign non-consumable items to users and track their usage',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Non-consumable items can be assigned to specific users for tracking and accountability. This feature helps monitor equipment usage and responsibility.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Assigning an item:',
                        'steps' => [
                            'Go to the item detail page',
                            'Click "Assign Item" button',
                            'Select the user to assign the item to',
                            'Optionally specify location or notes',
                            'Click "Assign" to complete'
                        ]
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Unassigning an item:',
                        'steps' => [
                            'Go to the assigned item detail page',
                            'Click "Unassign Item" button',
                            'The item becomes available for reassignment'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Assigned items show the current holder in the item details and cannot be assigned to another user until unassigned.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Assignment Best Practices:',
                        'tips' => [
                            'Document the assignment reason in notes',
                            'Regularly review assigned items for returns',
                            'Update item conditions when returned',
                            'Use assignments to track valuable equipment'
                        ]
                    ]
                ],
                'tags' => ['assignment', 'non-consumable', 'tracking', 'users'],
                'roles' => ['admin', 'office_head']
            ],

            'trash-restore' => [
                'title' => 'Trash and Restore Items',
                'description' => 'How to soft-delete items and restore them from the trash',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The system uses soft deletion to safely remove items while preserving data integrity. Deleted items can be restored if needed.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Moving items to trash:',
                        'steps' => [
                            'Go to Items list or item detail page',
                            'Click the "Delete" button',
                            'Confirm the deletion',
                            'Item is moved to trash (soft deleted)'
                        ]
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Restoring from trash:',
                        'steps' => [
                            'Go to Items → Trash',
                            'Find the item to restore',
                            'Click "Restore" button',
                            'Item becomes active again'
                        ]
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Permanent deletion:',
                        'steps' => [
                            'Go to Items → Trash',
                            'Select items for permanent deletion',
                            'Click "Force Delete" (cannot be undone)',
                            'Item is completely removed from database'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Warning: Permanent deletion cannot be undone. Use with extreme caution.'
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Items in trash maintain their relationships and can be restored with all data intact.'
                    ]
                ],
                'tags' => ['trash', 'restore', 'delete', 'soft-delete'],
                'roles' => ['admin', 'office_head']
            ],

            'view-requests' => [
                'title' => 'Viewing and Managing Requests',
                'description' => 'How to view, filter, and manage all supply requests in the system',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The requests management interface allows you to view all requests in the system with powerful filtering and search capabilities.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Viewing requests:',
                        'steps' => [
                            'Navigate to "Requests" in the main menu',
                            'Use filters: status, priority, date range',
                            'Search by user name, item name, or claim slip number',
                            'Sort by date, status, or priority',
                            'Click on any request to view details'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Request Statuses:',
                        'content' => 'Pending → Under Review → Approved by Admin → Ready for Pickup → Fulfilled → Claimed'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Management Tips:',
                        'tips' => [
                            'Use status filters to focus on urgent requests',
                            'Check priority levels for time-sensitive items',
                            'Review request history and comments',
                            'Monitor fulfillment progress regularly'
                        ]
                    ]
                ],
                'tags' => ['requests', 'view', 'manage', 'filter', 'search'],
                'roles' => ['admin', 'office_head']
            ],

            'fulfill-requests' => [
                'title' => 'Fulfilling and Claiming Requests',
                'description' => 'How to fulfill approved requests and process item claims',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Fulfilling requests involves preparing items for pickup and generating claim slips. The system supports barcode verification for accuracy.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Fulfilling a request:',
                        'steps' => [
                            'Go to approved request details',
                            'Click "Fulfill Request" button',
                            'Verify item availability and stock',
                            'Scan QR code or enter barcode for verification',
                            'Generate claim slip number',
                            'Update stock levels (for consumables)',
                            'Mark request as fulfilled'
                        ]
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Processing claims:',
                        'steps' => [
                            'User presents claim slip',
                            'Scan claim slip QR code or enter number',
                            'Verify request details',
                            'Mark as claimed',
                            'Complete the transaction'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Barcode verification ensures the correct items are dispensed and prevents errors.'
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Always verify stock availability before fulfilling requests to prevent overselling.'
                    ]
                ],
                'tags' => ['fulfill', 'claim', 'barcode', 'verification'],
                'roles' => ['admin', 'office_head']
            ],

            'claim-slips' => [
                'title' => 'Claim Slips and Documentation',
                'description' => 'Understanding claim slips, printing, and documentation',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Claim slips serve as official documentation for item disbursement and provide QR codes for easy verification.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Claim slip process:',
                        'steps' => [
                            'Request is fulfilled by admin',
                            'Unique claim slip number is generated',
                            'QR code is created for the claim slip',
                            'Faculty receives notification',
                            'Faculty can print claim slip',
                            'Present claim slip for item pickup'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Claim Slip Information:',
                        'content' => 'Includes request details, items, quantities, claim slip number, QR code, and pickup instructions.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Best Practices:',
                        'tips' => [
                            'Always print claim slips for record keeping',
                            'Keep claim slips until items are received',
                            'Report any discrepancies immediately',
                            'Use QR codes for quick verification'
                        ]
                    ]
                ],
                'tags' => ['claim-slip', 'documentation', 'qr-code', 'printing'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'request-analytics' => [
                'title' => 'Request Analytics',
                'description' => 'Analyzing request patterns, approval times, and usage trends',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Request analytics provide insights into system usage, approval efficiency, and item popularity.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Using request analytics:',
                        'steps' => [
                            'Go to Reports → Request Analytics',
                            'Select time period (monthly, quarterly, annually)',
                            'Review approval rates and processing times',
                            'Analyze most requested items',
                            'View user request patterns',
                            'Export data for further analysis'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Analytics include approval times, request volumes, popular items, and user activity patterns.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Using Analytics Data:',
                        'tips' => [
                            'Identify bottlenecks in approval process',
                            'Stock popular items proactively',
                            'Monitor faculty department usage',
                            'Optimize inventory based on demand'
                        ]
                    ]
                ],
                'tags' => ['analytics', 'requests', 'reports', 'trends'],
                'roles' => ['admin', 'office_head']
            ],

            'user-activity-reports' => [
                'title' => 'User Activity Reports',
                'description' => 'Monitoring user engagement, scanning activity, and system usage',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'User activity reports show how users interact with the system, including QR scanning and request patterns.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Viewing user activity:',
                        'steps' => [
                            'Go to Reports → User Activity',
                            'Select time period and filters',
                            'View individual user statistics',
                            'Analyze scanning patterns',
                            'Review request activity',
                            'Export detailed reports'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Reports include scan counts, unique items scanned, request frequency, and activity trends.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Activity Insights:',
                        'tips' => [
                            'Identify power users and system champions',
                            'Monitor adoption rates across departments',
                            'Track QR code usage effectiveness',
                            'Identify training opportunities'
                        ]
                    ]
                ],
                'tags' => ['users', 'activity', 'reports', 'engagement'],
                'roles' => ['admin', 'office_head']
            ],

            'category-management' => [
                'title' => 'Managing Categories',
                'description' => 'How to create, edit, and organize item categories',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Categories help organize inventory items logically, making them easier to find and manage.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Managing categories:',
                        'steps' => [
                            'Go to Categories in the admin menu',
                            'Create new categories with descriptive names',
                            'Edit existing category information',
                            'Assign items to appropriate categories',
                            'View items grouped by category',
                            'Delete categories (only if empty)'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Categories cannot be deleted if they contain items. Reassign items first.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Category Best Practices:',
                        'tips' => [
                            'Use clear, hierarchical category names',
                            'Create categories based on item function or department',
                            'Avoid creating too many categories',
                            'Regularly review and consolidate categories',
                            'Use consistent naming conventions'
                        ]
                    ]
                ],
                'tags' => ['categories', 'organization', 'management'],
                'roles' => ['admin', 'office_head']
            ],

            'edit-request' => [
                'title' => 'Editing Pending Requests',
                'description' => 'How to modify requests that are still pending approval',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'You can edit your requests while they are still in pending status. Once approved, requests cannot be modified.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Editing a request:',
                        'steps' => [
                            'Go to My Requests section',
                            'Find the pending request',
                            'Click "Edit" button',
                            'Modify items, quantities, or purpose',
                            'Update priority or dates if needed',
                            'Save your changes'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'You cannot edit requests that have been approved or are being processed.'
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Contact your supply office administrator if you need to modify an approved request.'
                    ]
                ],
                'tags' => ['edit', 'requests', 'pending', 'modify'],
                'roles' => ['faculty', 'admin', 'office_head']
            ],

            'qr-scanning' => [
                'title' => 'QR Code Scanning',
                'description' => 'How to use QR codes for quick item lookup and verification',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'QR codes provide instant access to item information and enable efficient inventory management.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Scanning QR codes:',
                        'steps' => [
                            'Use device camera or QR scanner',
                            'Point at item QR code',
                            'System automatically retrieves item details',
                            'View stock status, location, and information',
                            'Log scan for inventory tracking'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'QR scanning works for both inventory checks and request fulfillment verification.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Scanning Tips:',
                        'tips' => [
                            'Ensure good lighting for accurate scanning',
                            'Hold device steady while scanning',
                            'Clean QR codes if scanning fails',
                            'Use scanning for regular inventory audits'
                        ]
                    ]
                ],
                'tags' => ['qr', 'scan', 'barcode', 'verification'],
                'roles' => ['faculty', 'admin', 'office_head']
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
            ],

            'edit-item' => [
                'title' => 'Editing Item Details',
                'description' => 'How to modify existing item information and settings',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Keeping item information accurate and up-to-date is essential for effective inventory management.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Editing an item:',
                        'steps' => [
                            'Navigate to the Items list or search for the item',
                            'Click on the item name or "Edit" button',
                            'Update any information that has changed',
                            'Modify stock levels if necessary',
                            'Update pricing or supplier information',
                            'Save your changes'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Changes to stock levels should be done carefully and only when physically verified.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Best Practices:',
                        'tips' => [
                            'Regularly review and update item descriptions',
                            'Keep supplier contact information current',
                            'Update pricing when costs change',
                            'Document any significant changes in item notes'
                        ]
                    ]
                ],
                'tags' => ['edit', 'update', 'inventory', 'items'],
                'roles' => ['admin', 'office_head']
            ],

            'stock-management' => [
                'title' => 'Managing Stock Levels',
                'description' => 'How to monitor and maintain appropriate stock levels',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Proper stock management ensures items are available when needed while preventing overstocking and waste.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Stock management process:',
                        'steps' => [
                            'Monitor stock levels through the dashboard alerts',
                            'Review low stock items regularly',
                            'Set appropriate minimum and maximum stock levels',
                            'Track stock movements and usage patterns',
                            'Plan reordering based on consumption rates',
                            'Update stock levels after physical inventory counts'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'The system automatically alerts you when items fall below minimum stock levels.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Stock Management Tips:',
                        'tips' => [
                            'Set minimum levels based on average monthly usage',
                            'Consider lead times when setting reorder points',
                            'Regular physical counts prevent discrepancies',
                            'Use reports to identify slow-moving items'
                        ]
                    ]
                ],
                'tags' => ['stock', 'inventory', 'levels', 'management'],
                'roles' => ['admin', 'office_head']
            ],

            'categories' => [
                'title' => 'Working with Categories',
                'description' => 'How to organize items using categories and subcategories',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Categories help organize inventory items logically, making them easier to find and manage.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Managing categories:',
                        'steps' => [
                            'Go to Categories in the admin menu',
                            'Create new categories for different item types',
                            'Assign items to appropriate categories',
                            'Use subcategories for better organization',
                            'Edit category names and descriptions as needed',
                            'View items grouped by category in reports'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Well-organized categories improve search efficiency and reporting accuracy.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Category Best Practices:',
                        'tips' => [
                            'Use clear, descriptive category names',
                            'Create categories based on item function or type',
                            'Avoid creating too many categories',
                            'Regularly review and consolidate categories',
                            'Use consistent naming conventions'
                        ]
                    ]
                ],
                'tags' => ['categories', 'organization', 'classification'],
                'roles' => ['admin', 'office_head']
            ],

            'bulk-operations' => [
                'title' => 'Bulk Item Operations',
                'description' => 'How to perform operations on multiple items simultaneously',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Bulk operations help you efficiently manage multiple items at once, saving time and reducing errors.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Common bulk operations:',
                        'steps' => [
                            'Select multiple items using checkboxes',
                            'Choose the operation from the bulk actions menu',
                            'Apply changes like category updates or status changes',
                            'Review changes before confirming',
                            'Monitor the progress of bulk operations'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Bulk operations cannot be easily undone. Always review changes carefully before applying.'
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Bulk operations are particularly useful for inventory updates, category assignments, and status changes.'
                    ]
                ],
                'tags' => ['bulk', 'operations', 'efficiency', 'multiple'],
                'roles' => ['admin', 'office_head']
            ],

            'approval-workflow' => [
                'title' => 'Understanding Approval Workflow',
                'description' => 'How the request approval process works',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The approval workflow ensures requests are properly reviewed and approved before fulfillment.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Approval workflow steps:',
                        'steps' => [
                            'Faculty submits request (Pending status)',
                            'Admin reviews request details and availability',
                            'Admin approves or rejects the request',
                            'If approved, request moves to Approved status',
                            'Items are prepared for pickup',
                            'Faculty receives notification and picks up items',
                            'Request is marked as fulfilled'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'The simplified workflow eliminates the office head approval step for faster processing.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Approval Guidelines:',
                        'tips' => [
                            'Check item availability before approving',
                            'Verify quantities are reasonable for the request',
                            'Consider urgency and priority levels',
                            'Add comments explaining approval decisions',
                            'Communicate with faculty if modifications are needed'
                        ]
                    ]
                ],
                'tags' => ['approval', 'workflow', 'process', 'requests'],
                'roles' => ['admin', 'office_head']
            ],

            'request-reports' => [
                'title' => 'Request Reports and Analytics',
                'description' => 'Generating reports on request patterns and performance',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Request reports help you understand usage patterns, identify trends, and optimize inventory management.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Accessing request reports:',
                        'steps' => [
                            'Go to Reports section in the admin menu',
                            'Select request-related reports',
                            'Choose date ranges and filters',
                            'View charts and statistics',
                            'Export reports for further analysis',
                            'Use insights to improve processes'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Reports include approval times, popular items, faculty usage patterns, and fulfillment metrics.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Using Report Data:',
                        'tips' => [
                            'Identify frequently requested items for stocking',
                            'Monitor approval processing times',
                            'Track faculty departments\' usage patterns',
                            'Use data to justify budget requests',
                            'Identify opportunities for process improvements'
                        ]
                    ]
                ],
                'tags' => ['reports', 'analytics', 'requests', 'metrics'],
                'roles' => ['admin', 'office_head']
            ],

            'dashboard-analytics' => [
                'title' => 'Dashboard Analytics',
                'description' => 'Understanding and using dashboard metrics and insights',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'The dashboard provides real-time analytics and key performance indicators to help you monitor system health.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Reading dashboard analytics:',
                        'steps' => [
                            'Review key statistics cards (users, items, requests)',
                            'Monitor low stock alerts and warnings',
                            'Check recent activity feed',
                            'View pending requests and approvals',
                            'Track system performance metrics',
                            'Identify trends and patterns'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Dashboard data updates in real-time to provide current system status.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Analytics Best Practices:',
                        'tips' => [
                            'Check dashboard daily for critical alerts',
                            'Use trends to predict inventory needs',
                            'Monitor approval backlog regularly',
                            'Review activity patterns for optimization',
                            'Export data for detailed analysis when needed'
                        ]
                    ]
                ],
                'tags' => ['dashboard', 'analytics', 'metrics', 'monitoring'],
                'roles' => ['admin', 'office_head']
            ],

            'inventory-reports' => [
                'title' => 'Inventory Reports',
                'description' => 'Comprehensive inventory analysis and reporting',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Inventory reports provide detailed insights into stock levels, usage patterns, and inventory health.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Using inventory reports:',
                        'steps' => [
                            'Access inventory reports from the Reports menu',
                            'Filter by category, status, or date range',
                            'Review stock level summaries',
                            'Identify low stock and overstock items',
                            'Analyze usage trends and patterns',
                            'Export reports for inventory planning'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Reports help optimize inventory investment and ensure adequate stock levels.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Inventory Report Insights:',
                        'tips' => [
                            'Use low stock reports to prevent shortages',
                            'Identify slow-moving items for potential removal',
                            'Track seasonal usage patterns',
                            'Monitor inventory turnover rates',
                            'Plan purchases based on consumption data'
                        ]
                    ]
                ],
                'tags' => ['inventory', 'reports', 'stock', 'analysis'],
                'roles' => ['admin', 'office_head']
            ],

            'qr-scan-analytics' => [
                'title' => 'QR Scan Analytics',
                'description' => 'Monitoring QR code scanning activities and item tracking analytics',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'QR scan analytics provide insights into item usage patterns, scanning frequency, and inventory tracking effectiveness.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Using QR scan analytics:',
                        'steps' => [
                            'Access QR Scan Analytics from the Reports menu',
                            'View scanning trends and patterns over time',
                            'Monitor item scanning frequency and popularity',
                            'Track user scanning activity and engagement',
                            'Identify items that need more attention',
                            'Analyze scanning locations and patterns',
                            'Export analytics data for further analysis'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Analytics include scan frequency, user activity, item popularity, location tracking, and usage trends.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Analytics Insights:',
                        'tips' => [
                            'Identify frequently scanned items for priority stocking',
                            'Monitor user engagement with the QR system',
                            'Track scanning patterns to optimize item placement',
                            'Identify items that may need better labeling',
                            'Use data to improve inventory management processes',
                            'Monitor system usage and adoption rates'
                        ]
                    ]
                ],
                'tags' => ['qr', 'scan', 'analytics', 'tracking', 'usage'],
                'roles' => ['admin', 'office_head']
            ],

            'export-data' => [
                'title' => 'Exporting Data',
                'description' => 'How to export system data for external analysis and reporting',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Data export functionality allows you to extract system data for external analysis, backup, or integration.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Exporting data:',
                        'steps' => [
                            'Go to Reports section for formatted exports',
                            'Use backup system for complete data exports',
                            'Choose appropriate export format (CSV, Excel, PDF)',
                            'Select date ranges and filters as needed',
                            'Download exported files securely',
                            'Store exports according to data retention policies'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Exports include inventory data, request history, user information, and activity logs.'
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Exported data may contain sensitive information. Handle and store exports securely.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Export Best Practices:',
                        'tips' => [
                            'Use filters to limit export size',
                            'Choose appropriate formats for your needs',
                            'Verify data integrity after export',
                            'Securely store and share exported files',
                            'Document export purposes for audit trails'
                        ]
                    ]
                ],
                'tags' => ['export', 'data', 'backup', 'analysis'],
                'roles' => ['admin', 'office_head']
            ],

            'user-management' => [
                'title' => 'User Management',
                'description' => 'Managing user accounts, roles, and permissions',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'User management involves creating, modifying, and controlling user access to the system.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Managing users:',
                        'steps' => [
                            'Access User Management from admin menu',
                            'View list of all system users',
                            'Create new user accounts with appropriate roles',
                            'Edit user information and permissions',
                            'Deactivate or reactivate user accounts',
                            'Monitor user activity and access patterns'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'User roles include: Admin (full access), Office Head (approval authority), Faculty (request access).'
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Always assign the minimum required permissions. Regularly review and update user access.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'User Management Best Practices:',
                        'tips' => [
                            'Use descriptive names for user accounts',
                            'Assign roles based on job responsibilities',
                            'Regularly review user access permissions',
                            'Deactivate accounts for departed users immediately',
                            'Monitor for unusual login patterns'
                        ]
                    ]
                ],
                'tags' => ['users', 'management', 'roles', 'permissions'],
                'roles' => ['admin']
            ],

            'system-settings' => [
                'title' => 'System Settings and Configuration',
                'description' => 'Configuring system-wide settings and preferences',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'System settings control global behavior, notifications, and operational parameters.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Configuring system settings:',
                        'steps' => [
                            'Access System Settings from admin menu',
                            'Review and update general system preferences',
                            'Configure notification settings and email templates',
                            'Set approval workflows and thresholds',
                            'Configure backup and maintenance schedules',
                            'Update system security settings'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Settings affect all users and should be changed carefully with proper testing.'
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: System changes can affect all users. Test changes in a development environment first.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Configuration Tips:',
                        'tips' => [
                            'Document all configuration changes',
                            'Test settings in non-production first',
                            'Keep backup of working configurations',
                            'Review settings periodically for optimization',
                            'Communicate major changes to users'
                        ]
                    ]
                ],
                'tags' => ['settings', 'configuration', 'system', 'admin'],
                'roles' => ['admin']
            ],

            'backup-restore' => [
                'title' => 'Backup and Restore Procedures',
                'description' => 'Creating backups and restoring system data when needed',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Regular backups ensure data safety and business continuity in case of system failures or data loss.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Backup procedures:',
                        'steps' => [
                            'Access backup system from admin menu',
                            'Schedule automatic backups (recommended daily)',
                            'Create manual backups before major changes',
                            'Verify backup integrity and completeness',
                            'Store backups in secure, off-site locations',
                            'Test restore procedures regularly'
                        ]
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Restore procedures:',
                        'steps' => [
                            'Identify the backup point for restoration',
                            'Stop the system to prevent data conflicts',
                            'Execute restore from verified backup',
                            'Verify data integrity after restoration',
                            'Restart system and test functionality',
                            'Document the restore process and results'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Restores can overwrite current data. Always backup current state before restoring.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Backup Best Practices:',
                        'tips' => [
                            'Follow 3-2-1 rule: 3 copies, 2 media types, 1 off-site',
                            'Test backups by performing restore drills',
                            'Automate backups where possible',
                            'Monitor backup success and alert on failures',
                            'Document backup and restore procedures'
                        ]
                    ]
                ],
                'tags' => ['backup', 'restore', 'data', 'recovery'],
                'roles' => ['admin']
            ],

            'troubleshooting' => [
                'title' => 'Troubleshooting Common Issues',
                'description' => 'Resolving common system problems and error conditions',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Most system issues can be resolved using systematic troubleshooting approaches.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'General troubleshooting steps:',
                        'steps' => [
                            'Identify the specific problem and symptoms',
                            'Check system status and error logs',
                            'Verify user permissions and access rights',
                            'Test with different browsers or devices',
                            'Clear browser cache and cookies',
                            'Contact technical support if issue persists'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Common issues include login problems, slow performance, data display errors, and permission issues.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Common Solutions:',
                        'tips' => [
                            'Login issues: Check username/password, account status',
                            'Slow performance: Clear cache, check internet connection',
                            'Permission errors: Verify user roles and access rights',
                            'Display issues: Try different browser, clear cache',
                            'Data errors: Check data integrity, refresh page'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Do not attempt advanced fixes unless you have proper technical training.'
                    ]
                ],
                'tags' => ['troubleshooting', 'issues', 'errors', 'support'],
                'roles' => ['admin', 'office_head', 'faculty']
            ],

            'track-request' => [
                'title' => 'Tracking Your Requests',
                'description' => 'How to monitor the status and progress of your supply requests',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'You can track all your supply requests to see their current status and approval progress.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Tracking requests:',
                        'steps' => [
                            'Go to My Requests section in your dashboard',
                            'View list of all your submitted requests',
                            'Click on a request to see detailed status',
                            'Check approval progress and current stage',
                            'View comments and updates from approvers',
                            'See expected fulfillment timeline'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Request statuses include: Pending, Under Review, Approved, Rejected, Fulfilled.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Tracking Tips:',
                        'tips' => [
                            'Check request status regularly for updates',
                            'Contact approver if request is delayed',
                            'Keep reference numbers for urgent requests',
                            'Review approval comments for feedback',
                            'Plan ahead for time-sensitive needs'
                        ]
                    ]
                ],
                'tags' => ['requests', 'tracking', 'status', 'progress'],
                'roles' => ['faculty']
            ],

            'request-status' => [
                'title' => 'Understanding Request Status',
                'description' => 'What different request statuses mean and next steps',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Understanding request statuses helps you know what to expect and when to follow up.'
                    ],
                    [
                        'type' => 'info',
                        'title' => 'Request Status Definitions:',
                        'content' => 'Pending: Request submitted, waiting for initial review. Under Review: Being evaluated by approver. Approved: Request approved, waiting for fulfillment. Rejected: Request denied (check comments). Fulfilled: Items delivered successfully.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'What to do for each status:',
                        'steps' => [
                            'Pending: Wait for review (usually 1-2 business days)',
                            'Under Review: Approver is evaluating - be patient',
                            'Approved: Items will be prepared for pickup/delivery',
                            'Rejected: Review denial reason and resubmit if needed',
                            'Fulfilled: Rate your experience and provide feedback'
                        ]
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Status Management:',
                        'tips' => [
                            'Most requests are processed within 3-5 business days',
                            'Urgent requests are prioritized when possible',
                            'Contact supply office for status questions',
                            'Keep communication professional and clear',
                            'Provide complete information to avoid delays'
                        ]
                    ]
                ],
                'tags' => ['status', 'requests', 'approval', 'workflow'],
                'roles' => ['faculty']
            ],

            'browse-items' => [
                'title' => 'Browsing Available Items',
                'description' => 'How to explore and discover available supply items',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Browse the complete catalog of available supplies to find what you need.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Browsing items:',
                        'steps' => [
                            'Go to Browse Items section',
                            'Use category filters to narrow down options',
                            'Scroll through item listings',
                            'Click item names for detailed information',
                            'Check availability and stock levels',
                            'Add items to cart or save for later'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Items are organized by categories like Office Supplies, Teaching Materials, Equipment, etc.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Browsing Tips:',
                        'tips' => [
                            'Use search if you know exactly what you need',
                            'Check item descriptions for specifications',
                            'Note any restrictions or special requirements',
                            'Compare similar items before requesting',
                            'Save frequently needed items to favorites'
                        ]
                    ]
                ],
                'tags' => ['browse', 'items', 'catalog', 'inventory'],
                'roles' => ['faculty']
            ],

            'search-filters' => [
                'title' => 'Using Search and Filters',
                'description' => 'Advanced search techniques and filtering options',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Use search and filters to quickly find specific items or narrow down options.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Using search and filters:',
                        'steps' => [
                            'Enter keywords in the search bar',
                            'Use filters for category, availability, price range',
                            'Combine multiple filters for precise results',
                            'Sort results by relevance, price, or name',
                            'Clear filters to start over',
                            'Save search criteria for future use'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Search works across item names, descriptions, and categories.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Search Best Practices:',
                        'tips' => [
                            'Use specific keywords (e.g., "printer paper" not "paper")',
                            'Try synonyms if initial search doesn\'t work',
                            'Use filters to reduce large result sets',
                            'Check spelling and try variations',
                            'Use advanced search for complex queries'
                        ]
                    ]
                ],
                'tags' => ['search', 'filters', 'find', 'items'],
                'roles' => ['faculty']
            ],

            'item-details' => [
                'title' => 'Viewing Item Details',
                'description' => 'Understanding item specifications and requesting information',
                'content' => [
                    [
                        'type' => 'text',
                        'content' => 'Item detail pages provide complete information to help you make informed requests.'
                    ],
                    [
                        'type' => 'steps',
                        'title' => 'Reading item details:',
                        'steps' => [
                            'Click on any item name or image',
                            'Review specifications and description',
                            'Check current stock availability',
                            'Read usage guidelines and restrictions',
                            'View related items or alternatives',
                            'Add to cart or request directly'
                        ]
                    ],
                    [
                        'type' => 'info',
                        'content' => 'Details include specifications, current stock, usage notes, and any special requirements.'
                    ],
                    [
                        'type' => 'tips',
                        'title' => 'Item Selection Tips:',
                        'tips' => [
                            'Read specifications carefully for compatibility',
                            'Check for any usage restrictions or approvals needed',
                            'Compare with similar items if available',
                            'Note any special handling requirements',
                            'Consider quantity limits and request justification'
                        ]
                    ],
                    [
                        'type' => 'warning',
                        'content' => 'Important: Some items require special approval or have quantity restrictions.'
                    ]
                ],
                'tags' => ['details', 'specifications', 'items', 'information'],
                'roles' => ['faculty']
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

    /**
     * Submit feedback via email
     */
    public function submitFeedback(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|in:bug,feature',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'screenshots.*' => 'nullable|image|max:5120', // 5MB max per image
        ]);

        try {
            $user = Auth::user();
            $type = $validatedData['type'] === 'bug' ? 'Bug Report' : 'Feature Request';

            // Prepare email content
            $subject = "[$type] " . $validatedData['subject'];
            $body = "Type: $type\n";
            $body .= "From: {$user->name} ({$user->email})\n";
            $body .= "Role: " . ucfirst($user->role) . "\n";
            $body .= "Date: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $body .= "Message:\n" . $validatedData['message'] . "\n\n";
            $body .= "Browser: " . ($request->header('User-Agent') ?? 'Unknown') . "\n";
            $body .= "URL: " . url()->current() . "\n";

            // Handle file attachments
            $attachments = [];
            if ($request->hasFile('screenshots')) {
                foreach ($request->file('screenshots') as $file) {
                    $attachments[] = $file->getRealPath();
                }
            }

            // Send email to abuabujanny99@gmail.com
            Mail::raw($body, function ($message) use ($subject, $attachments) {
                $message->to('abuabujanny99@gmail.com')
                        ->subject($subject);

                // Attach screenshots if any
                foreach ($attachments as $attachment) {
                    $message->attach($attachment);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Feedback sent successfully! Thank you for helping us improve the system.'
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback submission error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $validatedData
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send feedback. Please try again later.'
            ], 500);
        }
    }
}