# Design Notes - Todo List Tracker Application

## Design Overview
A modern, responsive todo list tracker featuring a dashboard, kanban board, calendar view, and comprehensive task management. Built with Bootstrap 5.3 components from https://getbootstrap.com/.

## Color Scheme & Branding

### Primary Colors
- **Primary**: Bootstrap's primary blue (`#0d6efd`) - for main actions, links
- **Success**: Green (`#198754`) - for completed tasks
- **Warning**: Yellow (`#ffc107`) - for pending/due soon tasks
- **Danger**: Red (`#dc3545`) - for overdue tasks
- **Info**: Cyan (`#0dcaf0`) - for in-progress tasks

### Task Status Colors
- **Pending**: Warning (yellow badge)
- **In Progress**: Info (cyan badge)
- **Completed**: Success (green badge with checkmark)

### Priority Colors
- **Low**: Secondary (`#6c757d`)
- **Medium**: Warning (`#ffc107`)
- **High**: Danger (`#dc3545`)

## Layout Structure

### Global Layout
Based on Bootstrap's Dashboard example: https://getbootstrap.com/docs/5.3/examples/dashboard/

```
┌─────────────────────────────────────────┐
│           Top Navigation Bar             │
├──────┬──────────────────────────────────┤
│      │                                   │
│ Side │      Main Content Area            │
│ Nav  │                                   │
│      │                                   │
└──────┴──────────────────────────────────┘
```

### Navigation Structure

#### Top Navbar
- **Component**: Navbar (Fixed top)
- **Reference**: https://getbootstrap.com/docs/5.3/components/navbar/
- **Elements**:
  - Brand/Logo (left)
  - Search bar (center) - for quick task search
  - User dropdown (right) - profile, settings, logout
  - Notification bell icon with badge
  - Theme toggle (light/dark mode)

```html
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">TodoTracker</a>
    <button class="navbar-toggler" ...>
    <div class="collapse navbar-collapse">
      <form class="d-flex mx-auto" style="width: 50%;">
        <input class="form-control" type="search" placeholder="Search tasks...">
      </form>
      <ul class="navbar-nav ms-auto">
        <!-- Notifications, Profile dropdown -->
      </ul>
    </div>
  </div>
</nav>
```

#### Sidebar Navigation
- **Component**: Offcanvas/Sidebar
- **Reference**: https://getbootstrap.com/docs/5.3/examples/sidebars/
- **Width**: 250px on desktop, collapsible on mobile
- **Menu Items**:
  - Dashboard (icon: speedometer)
  - All Tasks (icon: list)
  - Kanban Board (icon: columns)
  - Calendar (icon: calendar)
  - Categories/Tags (icon: tags)
  - Archived (icon: archive)
  - Settings (icon: gear)

```html
<div class="sidebar border-end bg-light" style="width: 250px; min-height: 100vh;">
  <div class="list-group list-group-flush">
    <a href="#" class="list-group-item list-group-item-action active">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
    <!-- More items -->
  </div>
</div>
```

## Page Layouts

### 1. Dashboard View

#### Layout Grid
- **Reference**: https://getbootstrap.com/docs/5.3/examples/dashboard/
- **Structure**: 2-3 column layout with cards

#### Top Statistics Row
- **Component**: Cards with stats
- **Reference**: https://getbootstrap.com/docs/5.3/components/card/
- **Layout**: 4 columns on desktop, stacked on mobile

```html
<div class="row g-3 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card text-white bg-primary">
      <div class="card-body">
        <h5 class="card-title">Total Tasks</h5>
        <h2 class="mb-0">47</h2>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6">
    <div class="card text-white bg-warning">
      <div class="card-body">
        <h5 class="card-title">Pending</h5>
        <h2 class="mb-0">23</h2>
      </div>
    </div>
  </div>
  <!-- Repeat for In Progress, Completed -->
</div>
```

#### Recent Tasks Section
- **Component**: List Group
- **Reference**: https://getbootstrap.com/docs/5.3/components/list-group/
- **Features**:
  - Task title and description
  - Priority badge
  - Status badge
  - Due date
  - Action buttons (edit, delete)

```html
<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Recent Tasks</h5>
  </div>
  <ul class="list-group list-group-flush">
    <li class="list-group-item d-flex justify-content-between align-items-start">
      <div class="ms-2 me-auto">
        <div class="fw-bold">Task Title</div>
        <small class="text-muted">Task description...</small>
        <div class="mt-1">
          <span class="badge bg-danger">High</span>
          <span class="badge bg-info">In Progress</span>
        </div>
      </div>
      <div>
        <small class="text-muted">Due: Oct 25</small>
        <div class="btn-group btn-group-sm mt-1">
          <button class="btn btn-outline-primary">Edit</button>
          <button class="btn btn-outline-danger">Delete</button>
        </div>
      </div>
    </li>
  </ul>
</div>
```

#### Task Progress Chart
- **Component**: Progress bars
- **Reference**: https://getbootstrap.com/docs/5.3/components/progress/
- **Display**: Overall completion percentage

#### Upcoming Deadlines
- **Component**: Timeline-style list
- **Shows**: Tasks due in next 7 days
- **Style**: List group with colored left border for priority

### 2. Kanban Board View

#### Layout
- **Reference**: Bootstrap Grid + Cards
- **Structure**: 3-4 columns (Pending, In Progress, Completed, Archived)
- **Horizontal scroll**: On mobile devices

```html
<div class="row g-3">
  <div class="col-lg-3 col-md-6">
    <div class="card">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Pending <span class="badge bg-dark">12</span></h5>
      </div>
      <div class="card-body p-2" style="max-height: 70vh; overflow-y: auto;">
        <!-- Kanban task cards -->
        <div class="card mb-2 shadow-sm">
          <div class="card-body p-2">
            <h6 class="card-title">Task Title</h6>
            <p class="card-text small">Brief description...</p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge bg-danger">High</span>
              <small class="text-muted">Oct 25</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Repeat for other columns -->
</div>
```

#### Kanban Card Features
- Draggable cards (use HTMX for drag-drop updates)
- Color-coded priority indicator (left border)
- Assignee avatar (if multi-user)
- Due date badge
- Task tags/categories
- Quick action buttons on hover

### 3. Calendar View

#### Calendar Header
- **Component**: Buttons + Dropdown
- **Navigation**: Previous/Next month buttons
- **View options**: Month, Week, Day views

#### Calendar Grid
- **Component**: Table
- **Reference**: https://getbootstrap.com/docs/5.3/content/tables/
- **Structure**: 7-column grid (days of week)

```html
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <div class="btn-group">
        <button class="btn btn-outline-primary btn-sm">← Prev</button>
        <button class="btn btn-outline-primary btn-sm">Today</button>
        <button class="btn btn-outline-primary btn-sm">Next →</button>
      </div>
      <h4 class="mb-0">October 2025</h4>
      <div class="btn-group">
        <button class="btn btn-outline-secondary btn-sm active">Month</button>
        <button class="btn btn-outline-secondary btn-sm">Week</button>
        <button class="btn btn-outline-secondary btn-sm">Day</button>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>Sun</th><th>Mon</th><!-- ... --><th>Sat</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="p-2 align-top" style="height: 100px;">
            <div class="fw-bold">1</div>
            <div class="badge bg-warning text-dark w-100 text-start small">
              Task title
            </div>
          </td>
          <!-- More days -->
        </tr>
      </tbody>
    </table>
  </div>
</div>
```

#### Calendar Day Cell
- Date number (top)
- Task indicators (colored dots or mini badges)
- Click to see day's tasks
- Different background for today
- Muted background for other months

### 4. Task List View (All Tasks)

#### Filters & Sorting
- **Component**: Button group + Dropdowns
- **Reference**: https://getbootstrap.com/docs/5.3/components/button-group/
- **Options**:
  - Filter by: Status, Priority, Category, Date range
  - Sort by: Due date, Created date, Priority, Title
  - View: List or Grid

```html
<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
      Filter: All Status
    </button>
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
      Priority: All
    </button>
  </div>
  <div class="btn-group">
    <button class="btn btn-sm btn-outline-secondary">List</button>
    <button class="btn btn-sm btn-outline-secondary active">Grid</button>
  </div>
</div>
```

#### Task Cards (Grid View)
- **Component**: Cards in grid
- **Layout**: 3 columns on desktop, 1 on mobile

```html
<div class="row g-3">
  <div class="col-lg-4 col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
          <span class="badge bg-danger">High</span>
          <span class="badge bg-info">In Progress</span>
        </div>
        <h5 class="card-title">Task Title</h5>
        <p class="card-text">Task description goes here...</p>
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            <i class="bi bi-calendar"></i> Oct 25, 2025
          </small>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary">Edit</button>
            <button class="btn btn-outline-success">✓</button>
            <button class="btn btn-outline-danger">×</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

#### Task Table (List View)
- **Component**: Table
- **Reference**: https://getbootstrap.com/docs/5.3/content/tables/
- **Features**: Striped, hoverable rows, responsive

## Forms & Modals

### Add/Edit Task Modal
- **Component**: Modal
- **Reference**: https://getbootstrap.com/docs/5.3/components/modal/
- **Size**: Large modal
- **Form Elements**:
  - Task title (required)
  - Description (textarea)
  - Status (select dropdown)
  - Priority (select dropdown)
  - Due date (date picker)
  - Category/Tags (select with tags)
  - Attachments (file upload)

```html
<div class="modal fade" id="taskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label class="form-label">Task Title *</label>
            <input type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Status</label>
              <select class="form-select">
                <option>Pending</option>
                <option>In Progress</option>
                <option>Completed</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Priority</label>
              <select class="form-select">
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Save Task</button>
      </div>
    </div>
  </div>
</div>
```

### Quick Add Form (Dashboard)
- **Component**: Input group + Button
- **Reference**: https://getbootstrap.com/docs/5.3/forms/input-group/
- **Location**: Top of dashboard, always visible

```html
<div class="card mb-4">
  <div class="card-body">
    <div class="input-group input-group-lg">
      <input type="text" class="form-control" placeholder="Quick add a task...">
      <button class="btn btn-primary" type="button">
        <i class="bi bi-plus-lg"></i> Add Task
      </button>
    </div>
  </div>
</div>
```

## Interactive Components

### Toast Notifications
- **Component**: Toast
- **Reference**: https://getbootstrap.com/docs/5.3/components/toasts/
- **Usage**: Success/error feedback
- **Position**: Top-right corner

```html
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div class="toast" role="alert">
    <div class="toast-header">
      <strong class="me-auto">Success</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">
      Task created successfully!
    </div>
  </div>
</div>
```

### Confirmation Dialogs
- **Component**: Modal (small)
- **Usage**: Delete confirmations, important actions

### Loading States
- **Component**: Spinner
- **Reference**: https://getbootstrap.com/docs/5.3/components/spinners/
- **Usage**: During HTMX requests

```html
<div class="htmx-indicator">
  <div class="spinner-border text-primary" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>
```

### Badges
- **Component**: Badges
- **Reference**: https://getbootstrap.com/docs/5.3/components/badge/
- **Usage**: Status, priority, counts

## Additional Features

### Search Results
- **Component**: Dropdown menu (live search)
- **Reference**: https://getbootstrap.com/docs/5.3/components/dropdowns/
- **Appears**: Below search input as you type

### Filters Panel
- **Component**: Offcanvas
- **Reference**: https://getbootstrap.com/docs/5.3/components/offcanvas/
- **Trigger**: Filter button
- **Content**: All filter options with checkboxes

### Settings Page
- **Component**: Form + Accordion
- **Reference**: https://getbootstrap.com/docs/5.3/components/accordion/
- **Sections**:
  - Profile settings
  - Notification preferences
  - Theme settings
  - Data management

### Task Categories/Tags
- **Component**: Pills
- **Reference**: https://getbootstrap.com/docs/5.3/components/badge/#pills
- **Display**: Colored pill badges
- **Management**: Modal for CRUD operations

## Responsive Design

### Breakpoints
- **Mobile**: < 576px (stack all cards, hamburger menu)
- **Tablet**: 576px - 991px (2-column layout)
- **Desktop**: ≥ 992px (full layout with sidebar)

### Mobile Optimizations
- Collapsible sidebar (offcanvas)
- Bottom navigation for key actions
- Swipe gestures for kanban board
- Simplified forms
- Larger touch targets (min 44x44px)

### Desktop Enhancements
- Keyboard shortcuts
- Hover effects
- Multi-select capabilities
- Drag-and-drop functionality

## Icons

### Icon Library
- **Bootstrap Icons**: https://icons.getbootstrap.com/
- **CDN**: Include Bootstrap Icons CSS
- **Usage**: `<i class="bi bi-icon-name"></i>`

### Key Icons
- Dashboard: `bi-speedometer2`
- Tasks: `bi-list-task`
- Calendar: `bi-calendar3`
- Add: `bi-plus-circle`
- Edit: `bi-pencil`
- Delete: `bi-trash`
- Check: `bi-check-circle`
- Clock: `bi-clock`
- Tag: `bi-tag`
- Filter: `bi-funnel`

## Animation & Transitions

### CSS Transitions
- Smooth hover effects (0.3s ease)
- Card elevations on hover
- Button state changes
- Sidebar collapse/expand

### HTMX Transitions
- **Reference**: https://htmx.org/examples/animations/
- Fade in/out for content swaps
- Slide transitions for modals
- Loading indicators

## Accessibility

### Requirements
- Semantic HTML5 elements
- ARIA labels for icon-only buttons
- Keyboard navigation support
- Focus indicators
- Color contrast ratio ≥ 4.5:1
- Screen reader friendly text

### Bootstrap Built-in
- Form validation feedback
- Visually hidden labels where needed
- Proper heading hierarchy
- Skip navigation links

## Dark Mode Support (Optional)

### Implementation
- Bootstrap's data-bs-theme attribute
- Toggle button in navbar
- LocalStorage to persist preference
- Custom CSS variables for custom colors

```html
<html data-bs-theme="dark">
```

## Performance Considerations

### Optimization
- Lazy load calendar months
- Paginate task lists (20-50 items)
- Minimize DOM updates with HTMX
- Use Alpine.js for local state only
- Optimize images and assets

### Loading Strategy
- Critical CSS inline
- Defer non-critical JavaScript
- Use CDN for Bootstrap libraries
- Enable gzip compression

## Bootstrap Component Reference

### Primary Components Used
1. **Navbar**: https://getbootstrap.com/docs/5.3/components/navbar/
2. **Cards**: https://getbootstrap.com/docs/5.3/components/card/
3. **List Groups**: https://getbootstrap.com/docs/5.3/components/list-group/
4. **Modal**: https://getbootstrap.com/docs/5.3/components/modal/
5. **Forms**: https://getbootstrap.com/docs/5.3/forms/overview/
6. **Tables**: https://getbootstrap.com/docs/5.3/content/tables/
7. **Buttons**: https://getbootstrap.com/docs/5.3/components/buttons/
8. **Badges**: https://getbootstrap.com/docs/5.3/components/badge/
9. **Progress**: https://getbootstrap.com/docs/5.3/components/progress/
10. **Toasts**: https://getbootstrap.com/docs/5.3/components/toasts/
11. **Offcanvas**: https://getbootstrap.com/docs/5.3/components/offcanvas/
12. **Dropdowns**: https://getbootstrap.com/docs/5.3/components/dropdowns/

## Design Principles

1. **Consistency**: Use Bootstrap's design system throughout
2. **Clarity**: Clear visual hierarchy with typography scale
3. **Efficiency**: Minimize clicks to complete tasks
4. **Feedback**: Immediate visual feedback for all actions
5. **Accessibility**: Ensure usability for all users
6. **Responsiveness**: Mobile-first, progressive enhancement
7. **Performance**: Fast load times, smooth interactions