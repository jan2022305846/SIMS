# Supply Management System - ERD Creation Guide

## 📋 System Overview
This is a Laravel-based Supply Inventory Management System with the following key features:
- **User Management**: Admin, Faculty, and Office-based users
- **Item Management**: Consumable and Non-Consumable items with categories
- **Request Workflow**: Multi-step approval process for item requests
- **Inventory Tracking**: QR code scanning, stock management, and audit logs
- **Notification System**: Real-time notifications for request status updates

## 🗂️ Database Tables Summary

### Core Business Entities (9 tables)
1. **offices** - Office/department management
2. **users** - User accounts with role-based access
3. **categories** - Item categorization
4. **consumables** - Consumable inventory items
5. **non_consumables** - Non-consumable inventory items
6. **requests** - Item request workflow
7. **logs** - General activity logging
8. **item_scan_logs** - QR scan tracking
9. **notifications** - User notifications

### Framework Tables (8 tables)
- Laravel authentication, caching, queues, sessions, etc.

## 🔗 Entity Relationships

### Primary Relationships:
```
users (many) → offices (one) [belongsTo]
consumables (many) → categories (one) [belongsTo]
non_consumables (many) → categories (one) [belongsTo]
non_consumables (many) → users (one) [current_holder_id] [belongsTo]

requests (many) → users (one) [belongsTo - requester]
requests (many) → offices (one) [belongsTo]
requests (many) → users (one) [approved_by_admin_id] [belongsTo]
requests (polymorphic) → consumables/non_consumables [morphTo]

logs (many) → users (one) [belongsTo]
item_scan_logs (many) → users (one) [belongsTo]
item_scan_logs (many) → offices (one) [location_id] [belongsTo]
item_scan_logs (polymorphic) → consumables/non_consumables [morphTo]

notifications (many) → users (one) [belongsTo]
```

## 🛠️ Step-by-Step ERD Creation Guide

### Method 1: Using Draw.io (Free & Recommended)

1. **Open Draw.io**: Go to [draw.io](https://app.diagrams.net/)

2. **Create New Diagram**:
   - Choose "Entity Relationship Diagram" template
   - Or start with blank diagram and add ERD shapes

3. **Add Entities (Tables)**:
   - Use "Entity" shape from ERD section
   - Create one entity for each table
   - Label with table name

4. **Add Attributes (Columns)**:
   - Double-click entity to edit
   - Add column names with types
   - Mark Primary Keys (PK) and Foreign Keys (FK)
   - Example format:
     ```
     + id: BIGINT (PK)
     + name: VARCHAR(255)
     - office_id: BIGINT (FK)
     ```

5. **Create Relationships**:
   - Use "Relationship" connector
   - Cardinality notation:
     - `1` = One
     - `*` = Many
     - `0..1` = Zero or One
     - `1..*` = One or Many

6. **Color Coding** (Optional):
   - **Blue**: Core business entities
   - **Green**: Transaction/Workflow entities
   - **Orange**: Logging/Audit entities
   - **Gray**: Framework tables

### Method 2: Using Lucidchart

1. **Sign up**: Create free account at [lucidchart.com](https://www.lucidchart.com)
2. **New Document** → **ER Diagram**
3. **Import SQL** (if available) or manually create entities
4. **Use Crow's Foot notation** for relationships

### Method 3: Using dbdiagram.io (Online SQL to ERD)

1. **Go to**: [dbdiagram.io](https://dbdiagram.io)
2. **Paste the SQL schema** from `database_schema.sql`
3. **Auto-generate ERD**
4. **Customize** colors and layout

### Method 4: Using MySQL Workbench

1. **Install MySQL Workbench**
2. **Connect to your database**
3. **Database** → **Reverse Engineer**
4. **Select your schema** → **Execute**
5. **View** → **ER Diagram**

## 📊 ERD Layout Recommendations

### Group Tables Logically:

**Top Section - User Management:**
```
offices ← users
```

**Middle Section - Inventory:**
```
categories ← consumables
categories ← non_consumables → users (current_holder)
```

**Bottom Section - Transactions:**
```
users → requests → (consumables | non_consumables)
```

**Right Section - Logging:**
```
logs, item_scan_logs, notifications
```

### Relationship Legend:
- **Solid Line**: Strong relationship (required)
- **Dashed Line**: Optional relationship
- **Crow's Foot**: "Many" side of relationship
- **Vertical Line**: "One" side of relationship

## 🎯 Key Relationships to Highlight:

1. **Polymorphic Relationships**:
   - `requests.item_id` → `consumables.id` OR `non_consumables.id`
   - `item_scan_logs.item_id` → `consumables.id` OR `non_consumables.id`

2. **Self-Referencing**:
   - `users` can approve other `users` (admin approval)

3. **Workflow States**:
   - Request status progression: pending → approved_by_admin → fulfilled → claimed

4. **Audit Trail**:
   - Multiple log tables track all system activities

## 📋 ERD Checklist

- [ ] All 17 tables included
- [ ] Primary keys identified
- [ ] Foreign keys mapped
- [ ] Relationship cardinalities correct
- [ ] Polymorphic relationships shown
- [ ] Optional vs required relationships distinguished
- [ ] Table grouped logically
- [ ] Clear, readable layout
- [ ] Proper naming conventions
- [ ] Data types included

## 🔧 Tools & Resources

### Free Tools:
- **Draw.io**: Most flexible, works offline
- **dbdiagram.io**: Quick SQL import
- **Lucidchart**: Professional look (limited free)

### Paid Tools:
- **MySQL Workbench**: Database-specific
- **ERwin**: Enterprise-grade
- **Visual Paradigm**: UML & ERD support

## 📝 Pro Tips

1. **Start Simple**: Create basic structure first, then add details
2. **Use Consistent Notation**: Stick to one ERD notation style
3. **Include Sample Data**: Add example values for clarity
4. **Version Control**: Save ERD with version numbers
5. **Documentation**: Add notes for complex relationships
6. **Review with Team**: Get feedback from developers/DBAs

## 🚀 Quick Start Template

```sql
-- Copy from database_schema.sql
-- Import into your preferred ERD tool
-- Customize colors and layout as needed
```

The schema file `database_schema.sql` contains the complete table structures ready for ERD generation!

## 📖 ERD Narrative Description

The data flow in the Entity Relationship Diagram (ERD) begins with the Users entity, which serves as the foundation of the system. Each user is uniquely identified by their ID and includes important attributes such as username, email, password, and timestamps for creation and updates. Users are essential actors within the system, performing various functions such as requesting items, scanning logs, and managing inventory.

The Users entity is connected to the offices entity through an employs relationship, which indicates that each user is assigned to a specific office. The Offices entity holds details such as office name, location, and creation date, serving as a reference point for where users operate within the organization.

Users also interact with the requests entity, where they can submit requests for items. Each request includes several attributes such as item_id, quantity, purpose, needed_date, status, priority, return_date, and attachments. The submits relationship highlights that a user can create multiple requests, each tied to a particular office and item. This ensures traceability of every transaction made by users within their respective departments.

The Requests entity links closely with both Consumable and Non_Consumable entities. Consumable items are those that can be used up, such as office supplies, while Non_Consumable items represent reusable assets like equipment or tools. Both entities share similar attributes including product_code, name, brand, quantity, unit, and stock thresholds (min_stock and max_stock). They are each classified under a specific Category, as shown by the classifies relationship connecting them to the Categories entity. Categories define the grouping and description of items, ensuring that each consumable and non-consumable item is properly categorized for easier management and reporting.

The Non_Consumable entity also has a holds relationship with Users, indicating that a particular user is the current holder of a specific non-consumable item. This helps in tracking item ownership, usage, and accountability. Attributes such as condition, location, and timestamps are maintained to ensure accurate monitoring of item history.

Additionally, the Item_Scan_Logs entity records all scanning activities performed within the system. Each log includes details such as action, location_id, notes, and timestamps, and it is linked to both Users and items (either Consumable or Non_Consumable) through performs and scanned_in relationships. This connection allows the system to maintain a detailed record of which user performed which action on a specific item at a given time.

The Notifications entity serves as a communication hub within the system, ensuring that users stay informed about important events and status changes. Each notification is uniquely identified and includes attributes such as type, title, message, data payload, and read status. Through the receives relationship, notifications are linked to specific users, allowing the system to deliver targeted alerts about request approvals, low stock warnings, claim confirmations, and other critical updates. This entity maintains timestamps for creation and read status, enabling users to track their notification history and ensuring timely communication across all system activities.

Overall, the narrative flow of data begins with Users who are associated with Offices and interact with various items through Requests. These items are systematically categorized under Categories and tracked through Non_Consumable and Consumable entities. All interactions, including item movements and updates, are documented in Item_Scan_Logs to ensure transparency, traceability, and accountability throughout the inventory management process.