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

                        $sections['request-management'] = [
                'title' => 'Request Management',
                'description' => 'Handling supply requests and approvals',
                'icon' => 'fas fa-clipboard-check',
                'topics' => [
                    'process-requests' => 'How to Process Requests',
                    'approval-workflow' => 'Understanding Approval Workflow',
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
                    'qr-scan-analytics' => 'QR Scan Analytics',
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
                    'request-status' => 'Understanding Request Status'
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
                            'Items - Browse and manage inventory items',
                            'Requests - Create and track supply requests',
                            'Reports - View analytics and generate reports',
                            'Users - Manage system users (admin only)',
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
}