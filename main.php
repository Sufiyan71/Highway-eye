 <?php
require_once 'db_operations.php';

// Initialize error display for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get vehicle type counts for dashboard cards
$vehicleCounts = getVehicleTypeCounts();

// Prepare vehicle types for filter dropdown
$vehicleTypes = getVehicleTypes();

// Initialize filter values from GET parameters
// Initialize filter values from GET parameters
$filters = [
    'vehicle_type' => $_GET['vehicle_type'] ?? '',
    'start_date' => $_GET['start_date'] ?? date('Y-m-d'), // Default to today
    'end_date' => $_GET['end_date'] ?? date('Y-m-d')      // Default to today
];

// Get current page from GET parameters (default to 1)
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20; // Records per page

// Get highway toll data with filters and pagination
$tollData = getHighwayTollData($filters, $currentPage, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Highway Toll Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <style>
        body { background-color: #f4f4f4; }
        .neumorphic { background: #f0f0f0; box-shadow: 10px 10px 20px #d1d1d1, -10px -10px 20px #ffffff; border-radius: 15px; }
        .sidebar { transition: width 0.3s ease, transform 0.3s ease; overflow: hidden; width: 80px; }
        .sidebar:hover { width: 250px; }
        .sidebar-icon { min-width: 20px; display: flex; justify-content: center; }
        .sidebar .sidebar-text { opacity: 0; width: 0; white-space: nowrap; transition: opacity 0.2s ease, width 0.2s ease; }
        .sidebar:hover .sidebar-text { opacity: 1; width: auto; margin-left: 10px; }
        .sidebar-item { transition: all 0.3s ease; display: flex; align-items: center; position: relative; overflow: hidden; box-shadow: 0 0 0 rgba(0,0,0,0); }
        .sidebar-item:hover { background-color: #f0f0f0; box-shadow: 3px 3px 6px #d1d1d1, -3px -3px 6px #ffffff; transform: translateY(-2px); }
        .sidebar-item:active { box-shadow: inset 2px 2px 5px #d1d1d1, inset -2px -2px 5px #ffffff; transform: translateY(0); }
        .sidebar-item::after { content: ''; position: absolute; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.1); top: 0; left: -100%; transition: all 0.3s ease; }
        .sidebar-item:hover::after { left: 100%; }
        .main-content { transition: margin-left 0.3s ease; margin-left: 80px; }
        .card-neumorphic {
            background: #f0f0f0;
            box-shadow: 5px 5px 10px #d1d1d1, -5px -5px 10px #ffffff;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .card-neumorphic:hover {
            box-shadow: 7px 7px 14px #c8c8c8, -7px -7px 14px #ffffff;
        }
        .pagination-btn {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 0.5rem;
            background: #f0f0f0;
            box-shadow: 3px 3px 6px #d1d1d1, -3px -3px 6px #ffffff;
            transition: all 0.2s ease;
        }
        .pagination-btn:hover {
            box-shadow: 4px 4px 8px #c8c8c8, -4px -4px 8px #ffffff;
        }
        .pagination-btn.active {
            background: #7C86CC;
            color: white;
            box-shadow: inset 2px 2px 5px #6b74b1, inset -2px -2px 5px #8d98e7;
        }
        .pagination-btn:active {
            box-shadow: inset 2px 2px 5px #d1d1d1, inset -2px -2px 5px #ffffff;
        }
        .form-input {
            background: #f0f0f0;
            border: none;
            box-shadow: inset 2px 2px 5px #d1d1d1, inset -2px -2px 5px #ffffff;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            box-shadow: inset 3px 3px 7px #c8c8c8, inset -3px -3px 7px #ffffff;
        }
        .form-select {
            background: #f0f0f0;
            border: none;
            box-shadow: inset 2px 2px 5px #d1d1d1, inset -2px -2px 5px #ffffff;
            border-radius: 8px;
            padding: 0.75rem;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            transition: all 0.3s ease;
        }
        .form-select:focus {
            outline: none;
            box-shadow: inset 3px 3px 7px #c8c8c8, inset -3px -3px 7px #ffffff;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            background: #7C86CC;
            color: white;
            font-weight: 600;
            box-shadow: 5px 5px 10px #d1d1d1, -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #8a93d6;
            box-shadow: 6px 6px 12px #c8c8c8, -6px -6px 12px #ffffff;
        }
        .btn:active {
            box-shadow: inset 3px 3px 7px #6b74b1, inset -3px -3px 7px #8d98e7;
        }
        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 5px 5px 10px #d1d1d1, -5px -5px 10px #ffffff;
        }
        .table-header {
            background: #7C86CC;
            color: white;
        }
        .table-row:nth-child(even) {
            background: #f8f8f8;
        }
        .table-row:nth-child(odd) {
            background: #f0f0f0;
        }
        .table-row:hover {
            background: #e8e8ff;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar fixed left-0 top-0 h-full neumorphic z-50">
        <div class="flex flex-col p-4 h-full">
            <div class="flex items-center mb-10">
                <div class="sidebar-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 200 200"> <circle cx="100" cy="100" r="80" fill="#7C86CC" /> <circle cx="100" cy="70" r="30" fill="#E6F7FF" /> <path d="M100,110 C130,110 150,130 160,170 H40 C50,130 70,110 100,110 Z" fill="#E6F7FF" /> </svg>
                </div>
                <div class="sidebar-text"> <h2 class="font-bold">Admin User</h2> <p class="text-sm text-gray-500">Administrator</p> </div>
            </div>
            <nav>
                <ul>
                    <li class="mb-3"><a href="main.php" class="sidebar-item p-2 rounded-lg bg-blue-50"><div class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg></div><span class="sidebar-text">Home</span></a></li>
                    <li class="mb-3"><a href="users.php" class="sidebar-item p-2 rounded-lg"><div class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" /></svg></div><span class="sidebar-text">Users</span></a></li>
                    <li class="mt-auto"><a href="logout.php" class="sidebar-item p-2 rounded-lg text-red-500 hover:bg-red-100"><div class="sidebar-icon"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L14.586 11H7a1 1 0 110-2h7.586l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div><span class="sidebar-text">Logout</span></a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="main-content flex-grow p-6 overflow-y-auto">
        <!-- Dashboard Header with Clock -->
        <div class="card-neumorphic p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Axle Dashboard</h1>
                </div>
                <div class="text-right">
                <p class="text-gray-500"><span id="current-date" class="font-medium">Loading...</span></p>
                    <p class="text-gray-500"> <span id="current-time" class="font-medium">Loading...</span></p>
                </div>
            </div>
        </div>
        
           <!-- Vehicle Count Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <?php 
    $vehicleTypeMap = ['car' => 'bg-blue-500', 'truck' => 'bg-green-500', 'bus' => 'bg-yellow-500'];
        // Add h-8 w-8 class for consistent sizing if needed, or adjust CSS
        $vehicleIconMap = [
            'car' => '<svg class="h-8 w-8" viewBox="0 0 120 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" preserveAspectRatio="xMidYMid meet" fill="currentColor" transform="matrix(-1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M114.19 74.5s.52-1.29 2.13-1.95c1.51-.62 5.04-.7 6.56 1.15c1.6 1.95 1.55 4.79 2.07 9.82c.41 3.88.77 8.21.14 10.19c-.47 1.49-1.85 2.95-5.05 3.04c-3.19.09-4.7-1.95-4.7-1.95l-1.15-20.3z" fill="#31322e"></path><path d="M4.39 82.44s-.46-2.67 1.97-5.1s4.06-2.67 7.18-2.9s14.6-.58 14.6-.58s25.15-27.81 25.38-28.04c.23-.23 51.68-.58 53.54-.58s4.98 1.51 5.45 4.17c.46 2.67 3 29.08 3 29.08L64.99 91.01l-60.6-8.57z" fill="#489df6"></path><path d="M58.46 93.15l-34.04-7.58l-19.81 16.09s-2.66-.27-2.66 2.39s-.27 7.31.27 7.71s94.27.8 94.27.8s22.2-.13 22.87-.53s.74-.87.93-5.05c.11-2.39.62-4.59-2.04-4.86c-.25-.02-.78-.04-1-.08c-2.1-.4-2.55-1.44-2.55-1.44l-8.91-14.23l-32.04 2.66l-15.29 4.12z" fill="#506d71"></path><path d="M28.09 96.99c-8.02-.13-13.97 5.55-13.97 13.71c0 6.85 4.66 12.84 13.06 13.19c9.18.39 14.22-6.71 14.35-12.67c.13-5.96-4.13-14.08-13.44-14.23z" fill="#4c443f"></path><path d="M27.57 103.06c-4.39.21-7.24 3.36-7.11 7.24c.13 3.88 3.1 7.5 7.5 7.37c4.4-.13 7.11-3.23 7.24-6.98c.12-3.62-2.2-7.89-7.63-7.63z" fill="#c8c8c8"></path><path d="M102.12 98.71c-6.77-4.29-14.81-2.55-19.07 4.41c-3.58 5.85-2.76 13.45 4.26 18.07c6.85 4.51 15.4 1.51 18.62-3.51c3.22-5.01 4.05-13.99-3.81-18.97z" fill="#4c443f"></path><path d="M98.38 103.41c-3.86-2.11-8.09-.43-9.85 2.47c-2.01 3.32-1.27 8.02 2.55 10.2c3.82 2.18 7.67.9 9.82-2.18c1.67-2.39 2.25-7.88-2.52-10.49z" fill="#c8c8c8"></path><path d="M4.02 101.68c.8.08 7.39-.05 7.39-.05s1.86-5.18 7.1-8.01c6.47-3.49 14.74-3.1 20.17.78c5.9 4.22 6.59 8.71 6.59 8.71l32.07-.18s1.81-6.85 8.02-10.73c5.81-3.63 14.7-3.12 19.79.65c6.47 4.78 7.11 9.18 7.11 9.18s4.48.02 5.02.02c.23 0-.49-11.27-.49-11.27l-1.29-12.29s-14.22-.13-22.63 2.2c-8.41 2.33-16.04 2.59-27.29 2.46c-11.25-.13-19.14-1.16-24.7-2.2c-5.56-1.03-13.32-1.81-20.17.13c-6.85 1.94-8.79 4.78-8.79 4.78s-7.76 4.01-8.02 4.78s.12 11.04.12 11.04z" fill="#1d86fb"></path><path d="M110.7 80.21c-.32.66-.11 3.66-.07 6.67c.03 2.48-.08 4.52.33 4.77c.39.25 5.98-.06 5.98-.06l-.97-10.81l-.21-.77c-.01 0-4.91-.09-5.06.2z" fill="#ff2a23"></path><path d="M6.68 81.12s8.71-.32 9.24.26c.53.58 0 2-1.06 4.01s-2.95 4.96-3.32 5.28c-.37.32-5.03.21-5.03.21L4.62 83.7l2.06-2.58z" fill="#ffffff"></path><path d="M4.04 81.17s-.53 2.05-.58 4.75c-.05 2.69.42 4.96.42 4.96h2.64s-.31-2.27-.26-4.81c.05-2.64.63-4.95.63-4.95l-2.85.05z" fill="#d7ccc5"></path><path d="M56.12 51.19c-1.7.92-6.58 7.28-9.08 10.31c-2.5 3.03-9.09 10.83-9.55 11.74c-.45.91-.3 2.05 1.14 2.12c1.44.08 39.17.45 39.7.38c.53-.08.76-.91.68-1.67c-.08-.76.38-20.68.23-21.59s-1.21-1.74-3.03-1.74c-1.6.01-19.18-.04-20.09.45z" fill="#506d73"></path><path d="M57.51 53.37c-.76.45-14.95 17.64-15.31 18.11c-.53.68-.54 1.24.36 1.24s32.12.08 32.88.08s1.21-.53 1.14-1.36c-.08-.83.38-16.97.23-17.42c-.15-.45-.53-.98-1.36-.98s-17.29-.05-17.94.33z" fill="#afe3fb"></path><path d="M86.81 50.75c-.61 0-1.82.53-1.82 1.67s.15 21.82.15 22.5s.45.76 1.59.76s20.91-.23 21.74-.23s.83-1.06.76-1.82c-.08-.76-2.5-19.85-2.65-21.06c-.15-1.21-1.06-1.97-2.27-2.05c-1.21-.08-17.5.23-17.5.23z" fill="#506d73"></path><path d="M44.01 75.26s1.52 2.35 2.63 3.44c1.04 1.02 2.53 1.85 3.96 1.73c1.43-.11 2.33-1.45 2.57-5.69c.27-4.69-.78-5.78-2.57-5.83c-1.83-.06-2.01.63-3.15 1.72c-.62.59-3.44 4.63-3.44 4.63z" fill="#0250ac"></path><path d="M48.4 75.65c1.17-.14 2.03-.83 2.78-2.61c.75-1.78.08-2.96-.67-3.4c-.84-.5-1.67-.33-1.87-.23c-.16.08-.82.71-1.48 1.51c-.9 1.08-1.66 2.24-1.66 2.24s1.25 2.69 2.9 2.49z" fill="#489df6"></path><path d="M87.87 53.32c-.29.29 0 19.39.23 19.55c.23.15 17.95.23 18.18 0c.23-.23-2.27-19.62-2.58-19.85c-.3-.23-15.53 0-15.83.3z" fill="#afe3fb"></path></g></svg>',
            'truck' => '<svg class="h-8 w-8" viewBox="0 0 120 128" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" preserveAspectRatio="xMidYMid meet" fill="currentColor" transform="matrix(-1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path fill="#5e6366" d="M63.44 97.98l-.03 11.39l10.8-.12l-.05-11.27z"></path><path fill="#808378" d="M84.89 96.4l-.14 17.34l36.08-.56l-.14-17.62z"></path><path fill="#808378" d="M4.02 113.91l76.64.25l-.09-9.03l-28.94.15l.14-7.47l-24.39-11.84l-23.37 20.46z"></path><path d="M1.31 105.99c.46.77 12.4.42 12.4.42l4.23-9.16l20.44.7l13.39-.14l.3-56.81s-9.04-2.11-9.18-1.55c-.14.56-2.68 8.03-2.68 8.03L15.68 48.9L9.2 65.67l-3.81 11.7V90.9L1 94.24s-.12 11.04.31 11.75z" fill="#d70617"></path><path d="M55.79 32.75c-.18.23-.2 1.22-.2 2.07c-.01 1.92-.02 4.36-.02 4.36h68.64s.03-2.15.02-3.92c-.01-1.15.11-2.15-.06-2.32c-.22-.22-18.12-.23-35.34-.25c-16.6-.01-32.83-.22-33.04.06z" fill="#c6c7c1"></path><path d="M60.59 94.57l-5.05 3.49s-.16 2.03.49 2.77c.31.35 15.39.08 31.59.01c17.58-.08 36.13-.1 36.37-.29c.46-.37.33-3.93.33-3.93l-63.73-2.05z" fill="#c6c7c1"></path><path d="M63.41 37.86l-7.84 1.32l.06 53.59l38.87 2.79l29.74-2.79l-.03-51.81l-32.92-3.33s-27.99-.23-27.88.23z" fill="#e1e1e1"></path><path d="M68.52 94.73l-2.5-.02c0-.49.37-49.15.37-54.56h2.5c0 5.42-.37 54.09-.37 54.58z" fill="#b0b0b0"></path><path fill="#b0b0b0" d="M81.135 93.756l.252-53.83l2.5.012l-.252 53.83z"></path><path fill="#b0b0b0" d="M96.05 40.02h2.5v54.2h-2.5z"></path><path fill="#b0b0b0" d="M110.998 40.153l2.5-.006l.125 53.83l-2.5.006z"></path><path fill="#4f8b26" d="M55.58 35.62l68.67.25v5.88l-68.67-.44z"></path><path d="M55.62 92.74s68.53-.3 68.63 0c.11.3.09 5.08.09 5.08l-68.8.24l.08-5.32z" fill="#4f8b26"></path><path d="M52.07 41.01s-4.34.55-6.7 2.92c-2.37 2.37-3.31 6.15-3.31 6.15l-24.76.23l-9.46 27.75l-.39 3.55v12.46l-6.49.28s.03-4.3.35-4.62s2.52-.16 2.52-.16s-.25-12.22.07-12.93c.32-.71 9.85-26.8 10.25-27.51s.79-2.44 2.52-2.52c1.73-.08 20.26-.39 20.26-.39s.16-6.31 5.28-10.33c5.19-4.07 10.01-3.15 10.01-3.15l-.15 8.27z" fill="#f92610"></path><path d="M38.93 97.94s.71-3.27-.99-4.51c-1.53-1.12-7.62-1.44-12.66-1.38c-6.37.08-9.38.39-11.82 4.21c-2.21 3.48-1.64 7.72-1.47 8.49c.29 1.42 1.3 2.36 2.65 2.3s2.6-.53 2.6-2.71c0-2.93 1.47-4.48 3.24-5.25s3.95-.94 7.61-.94s10.84-.21 10.84-.21z" fill="#f92610"></path><path d="M6.09 106.47c-.02.16.01-4.44-.01-7.88c-.01-3.13-.1-8.19.41-8.38c.45-.16 3.24-.41 3.72.18c.47.59.19 16.08.19 16.08s-1.16.04-2.09.04s-2.22-.04-2.22-.04z" fill="#c7c9c9"></path><path d="M23.44 49.81c-1.3.25-1.9 1-1.96 2.54c-.07 1.89.06 26.72.12 27.49c.06.77.59 1.85 3.01 1.89c4.31.06 8.61.12 10.2 0c1.24-.09 1.71-1.12 1.83-2.6c.12-1.47-.12-25.66-.06-26.9c.04-.89-.18-2.54-3.05-2.54c-1.05.01-9-.09-10.09.12z" fill="#c7c9c9"></path><path d="M25.23 52.49c-.9.21-1.15.83-1.19 2.1c-.05 1.56 0 21.97.04 22.6c.04.63.29 1.63 1.96 1.66c2.98.05 5.97.1 7.07 0c.86-.08 1.19-.93 1.27-2.14c.08-1.22-.08-21.19-.04-22.21c.03-.74-.12-2.09-2.11-2.09c-.74-.02-6.24-.1-7 .08z" fill="#b0e2fd"></path><path d="M18.59 51.64c-.11-1.23-.51-1.31-1.29-1.33c-.78-.02-1.31.15-1.72 1.1c-.41.94-8.55 24.18-8.67 24.89c-.07.45-.17 2.38-.16 3.83c.01.86-.04 1.38.54 1.64c.31.14 2.14.21 4.93.19c3.24-.02 5.01-.06 5.66-.77c.83-.9.57-3.96.59-6.08c.07-8.62.16-22.96.12-23.47z" fill="#c7c9c9"></path><path d="M16.46 55.95c-.53-.06-7.31 20.7-7.31 21.06s-.12 2.18 0 2.3c.19.19 1.79.19 3.36.18c1.57-.01 3.1-.03 3.36-.24c.53-.41.25-5.61.41-11.09c.18-5.96.46-12.18.18-12.21z" fill="#b0e2fd"></path><path d="M28.42 104.19c-6.36.11-10.35 5.03-9.87 10.73c.47 5.7 4.93 9.02 10.06 9.02c5.22 0 9.59-3.32 9.78-9.59c.18-6.27-4.28-10.26-9.97-10.16z" fill="#524745"></path><path d="M28.32 109.22c-2.37.07-4.37 1.67-4.46 4.65c-.07 2.32 1.8 4.28 4.37 4.46c3.03.22 4.84-2.04 4.75-4.65c-.08-2.04-1.43-4.56-4.66-4.46z" fill="#c8c8c8"></path><path d="M78.84 114.79c.29-6.35-4.37-10.64-10.09-10.53c-5.71.12-9.31 4.36-9.63 9.48c-.33 5.21 2.72 9.78 8.96 10.36c6.24.58 10.5-3.62 10.76-9.31z" fill="#524745"></path><path d="M73.5 114.27c.06-2.37-1.37-4.46-4.29-4.74c-2.28-.22-4.32 1.54-4.66 4.08c-.41 3.01 1.71 4.96 4.27 5.02c2.01.06 4.6-1.13 4.68-4.36z" fill="#c8c8c8"></path><path d="M103.67 112.35c-1.12-6.26-6.63-9.41-12.17-8.02c-5.55 1.38-8.11 6.32-7.29 11.38c.84 5.15 4.82 8.93 11.03 8.11s9.44-5.86 8.43-11.47z" fill="#524745"></path><path d="M98.49 113.26c-.45-2.33-2.35-4.04-5.3-3.66c-2.3.3-3.93 2.47-3.7 5.03c.27 3.03 2.8 4.45 5.35 3.94c2-.41 4.27-2.14 3.65-5.31z" fill="#c8c8c8"></path><path d="M111.14 123.76c6.32.67 10.89-3.73 11.11-9.44c.23-5.71-3.79-9.55-8.88-10.18c-5.18-.64-9.92 2.12-10.88 8.32c-.96 6.19 2.98 10.69 8.65 11.3z" fill="#524745"></path><path d="M111.97 118.45c2.37.2 4.54-1.1 4.99-4c.35-2.26-1.11-4.86-3.78-5.21c-3.01-.4-5.07 1.72-5.29 4.28c-.17 2.01.86 4.66 4.08 4.93z" fill="#c8c8c8"></path></g></svg>',
            'bus' => '<svg class="h-8 w-8" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 520.98 511.98" xml:space="preserve" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path style="fill:#FC6E51;" d="M479.981,245.318h-21.343c-5.875,0-10.656-15.452-10.656-21.327v-85.325 c0-17.655-14.359-31.999-31.999-31.999H31.999C14.343,106.667,0,121.011,0,138.667v181.338c0,17.624,14.343,31.998,31.999,31.998 h447.982c17.64,0,31.999-14.374,31.999-31.998v-42.687C511.98,259.677,497.621,245.318,479.981,245.318z"></path> <path style="fill:#E6E9ED;" d="M114.605,331.504l-18.609-22.187H0v10.688c0,17.624,14.343,31.998,31.999,31.998h76.372 C112.011,340.004,114.605,331.504,114.605,331.504z"></path> <g> <path style="fill:#434A54;" d="M95.996,298.66c-29.42,0-53.341,23.938-53.341,53.342c0,29.405,23.921,53.31,53.341,53.31 c29.405,0,53.326-23.904,53.326-53.31C149.322,322.598,125.401,298.66,95.996,298.66z"></path> <path style="fill:#434A54;" d="M394.64,298.66c-29.405,0-53.325,23.938-53.325,53.342c0,29.405,23.92,53.31,53.325,53.31 s53.342-23.904,53.342-53.31C447.982,322.598,424.046,298.66,394.64,298.66z"></path> </g> <g> <path style="fill:#F5F7FA;" d="M103.527,344.44c4.171,4.187,4.171,10.937,0,15.093c-4.156,4.156-10.922,4.156-15.078,0 c-4.171-4.156-4.171-10.906,0-15.093C92.605,340.285,99.371,340.285,103.527,344.44z"></path> <path style="fill:#F5F7FA;" d="M402.187,344.44c4.172,4.187,4.172,10.937,0,15.093s-10.922,4.156-15.078,0 c-4.172-4.156-4.172-10.906,0-15.093C391.265,340.285,398.015,340.285,402.187,344.44z"></path> </g> <polygon style="fill:#4FC2E9;" points="447.982,159.995 394.64,159.995 373.313,159.995 53.326,159.995 53.326,245.318 373.313,245.318 394.64,245.318 479.981,245.318 "></polygon> <path style="fill:#E6E9ED;" d="M479.981,277.318c-5.906,0-10.672,4.781-10.672,10.688c0,5.874,4.766,10.655,10.672,10.655h31.999 v-21.343H479.981z"></path> <g> <rect x="80" y="159.99" style="fill:#CCD1D9;" width="10.655" height="85.32"></rect> <rect x="111.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="143.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="175.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="207.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="239.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="271.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="303.99" y="159.99" style="fill:#CCD1D9;" width="10.655" height="85.32"></rect> <rect x="335.99" y="159.99" style="fill:#CCD1D9;" width="10.656" height="85.32"></rect> <rect x="367.99" y="159.99" style="fill:#CCD1D9;" width="10.655" height="85.32"></rect> </g> <rect x="53.33" y="197.32" style="fill:#E6E9ED;" width="325.31" height="10.672"></rect> <g> <path style="fill:#FFCE54;" d="M314.643,341.315h-10.655V165.322H186.649v175.994h-10.656V159.995c0-2.938,2.375-5.328,5.328-5.328 h127.995c2.938,0,5.327,2.391,5.327,5.328L314.643,341.315L314.643,341.315z"></path> <rect x="239.99" y="159.99" style="fill:#FFCE54;" width="10.656" height="181.32"></rect> <rect x="175.99" y="245.32" style="fill:#FFCE54;" width="138.65" height="106.68"></rect> </g> </g></svg>'
        ];
    
    $totalCount = 0;
    foreach ($vehicleCounts as $vehicle) {
        $totalCount += $vehicle['count'];
        $vehicleName = strtolower($vehicle['name']);
        $bgColor = $vehicleTypeMap[$vehicleName] ?? 'bg-purple-500';
        $icon = $vehicleIconMap[$vehicleName] ?? '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" /><path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>';
    ?>
    <div class="card-neumorphic p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="rounded-full p-3 <?php echo $bgColor; ?> bg-opacity-20 text-<?php echo str_replace('bg-', 'text-', $bgColor); ?>">
                    <?php echo $icon; ?>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-700"><?php echo ucfirst($vehicle['name']); ?></h2>
                    <p class="text-3xl font-bold"><?php echo number_format($vehicle['count']); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    
    <!-- Total Vehicles Card -->
    <div class="card-neumorphic p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="rounded-full p-3 bg-gray-500 bg-opacity-20 text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-xl font-bold text-gray-700">Total Vehicles</h2>
                    <p class="text-3xl font-bold"><?php echo number_format($totalCount); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Filters Section -->
        <div class="card-neumorphic p-6 mb-6">
            
            <form method="GET" action="main.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Vehicle Type</label>
                    <select id="vehicle_type" name="vehicle_type" class="form-select w-full">
                        <option value="">All Vehicles</option>
                        <?php foreach ($vehicleTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo ($filters['vehicle_type'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-input w-full datepicker" 
                           placeholder="Select start date" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-input w-full datepicker" 
                           placeholder="Select end date" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                </div>
                <div class="flex items-end gap-2">
                   <button type="submit" class="btn-neumorphic px-6 py-3 rounded-lg bg-grey-900 shadow-lg text-black" style="transition: background-color 0.3s, color 0.3s;"
                      onmouseover="this.style.backgroundColor='#7C86CC'; this.style.color='white';" 
                      onmouseout="this.style.backgroundColor=''; this.style.color='';">Apply Filters</button>
                   <a href="main.php" class="btn-neumorphic px-6 py-3 rounded-lg ml-2 bg-white-600 shadow-lg text-black"
                      style="transition: background-color 0.3s, color 0.3s;"
                      onmouseover="this.style.backgroundColor='#7C86CC'; this.style.color='white';" 
                      onmouseout="this.style.backgroundColor=''; this.style.color='';">
                      Reset
                   </a>
                </div>
            </form>
        </div>
        
        <!-- Data Table Section -->
        <div class="card-neumorphic p-6 mb-6">
            <!-- <h2 class="text-xl font-bold text-gray-700 mb-4">Highway Toll Data</h2> -->
             <!-- Dynamic Table Heading -->
<div class="mb-4">
    <h2 class="text-xl font-bold text-gray-700">
        <?php
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            if ($filters['start_date'] == date('Y-m-d') && $filters['end_date'] == date('Y-m-d')) {
                echo "Today's Records";
            } elseif ($filters['start_date'] == date('Y-m-d', strtotime('-1 day')) && 
                      $filters['end_date'] == date('Y-m-d', strtotime('-1 day'))) {
                echo "Yesterday's Records";
            } elseif ($filters['start_date'] == $filters['end_date']) {
                echo "Records for " . date('F j, Y', strtotime($filters['start_date']));
            } else {
                echo "Records from " . date('F j, Y', strtotime($filters['start_date'])) . 
                     " to " . date('F j, Y', strtotime($filters['end_date']));
            }
        } else {
            echo "All Records";
        }
        ?>
    </h2>
</div>
            
            <?php if (empty($tollData['data'])): ?>
                <div class="p-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xl">No records found</p>
                    
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="table-header">
                            <tr>
                                <th class="px-4 py-3 text-left">Lane</th>
                                <th class="px-4 py-3 text-left">Vehicle Number</th>
                                <th class="px-4 py-3 text-left">Vehicle Type</th>
                                <th class="px-4 py-3 text-left">Subtype</th>
                                <th class="px-4 py-3 text-left">Entry Time</th>
                                <th class="px-4 py-3 text-right">Toll Tax</th>
                                <th class="px-4 py-3 text-center">Media</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tollData['data'] as $row): ?>
                                <tr class="table-row border-b">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['lane_number']); ?></td>
                                    <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($row['vehicle_subtype']); ?></td>
                                    <td class="px-4 py-3"><?php echo formatTimestamp($row['entry_time']); ?></td>
                                    <td class="px-4 py-3 text-right font-medium">Rs <?php echo number_format($row['toll_tax'], 2); ?></td>
                                    <td class="px-4 py-3 text-center">
    <?php if (!empty($row['image_path'])): ?>
        <a href="javascript:void(0);" onclick="openModal('<?php echo htmlspecialchars($row['image_path']); ?>', 'image')" class="text-blue-600 hover:text-blue-800 mr-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 3C4.23858 3 2 5.23858 2 8V16C2 18.7614 4.23858 21 7 21H17C19.7614 21 22 18.7614 22 16V8C22 5.23858 19.7614 3 17 3H7Z" ></path>
                <path d="M19.8918 16.8014L17.8945 14.2809C16.9457 13.0835 15.2487 12.7904 13.9532 13.6001L13.1168 14.1228C12.6581 14.4095 12.0547 14.2795 11.7547 13.8295L10.3177 11.6741C9.20539 10.0056 6.80071 9.8771 5.51693 11.4176L4 13.238V16C4 17.6569 5.34315 19 7 19H17C18.3793 19 19.5412 18.0691 19.8918 16.8014Z" fill="#152C70"></path>
                <path d="M16 11C17.1046 11 18 10.1046 18 9C18 7.89543 17.1046 7 16 7C14.8954 7 14 7.89543 14 9C14 10.1046 14.8954 11 16 11Z" fill="#152C70"></path>
            </svg>
        </a>
    <?php endif; ?>
    <?php if (!empty($row['video_path'])): ?>
        <a href="javascript:void(0);" onclick="openModal('<?php echo htmlspecialchars($row['video_path']); ?>', 'video')" class="text-blue-600 hover:text-blue-800 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
            </svg>
        </a>
    <?php endif; ?>
</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-6 flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-gray-600 mb-4 md:mb-0">
                        Showing <?php echo $tollData['pagination']['from']; ?> to <?php echo $tollData['pagination']['to']; ?> of <?php echo $tollData['pagination']['total']; ?> records
                    </div>
                    <div class="flex flex-wrap justify-center">
                        <?php
                        $pagination = $tollData['pagination'];
                        $queryParams = $filters;
                        
                        // Previous button
                        if ($pagination['currentPage'] > 1): 
                            $queryParams['page'] = $pagination['currentPage'] - 1;
                            $prevUrl = 'main.php?' . http_build_query($queryParams);
                        ?>
                            <a href="<?php echo $prevUrl; ?>" class="pagination-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        // Page numbers
                        $startPage = max(1, $pagination['currentPage'] - 2);
                        $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                            $queryParams['page'] = $i;
                            $pageUrl = 'main.php?' . http_build_query($queryParams);
                            $activeClass = ($i == $pagination['currentPage']) ? ' active' : '';
                        ?>
                            <a href="<?php echo $pageUrl; ?>" class="pagination-btn<?php echo $activeClass; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php
                        // Next button
                        if ($pagination['currentPage'] < $pagination['totalPages']): 
                            $queryParams['page'] = $pagination['currentPage'] + 1;
                            $nextUrl = 'main.php?' . http_build_query($queryParams);
                        ?>
                            <a href="<?php echo $nextUrl; ?>" class="pagination-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
            // Media Modal Functions
function openModal(mediaPath, mediaType) {
    const modal = document.getElementById('mediaModal');
    const mediaContent = document.getElementById('mediaContent');
    
    // Clear previous content
    mediaContent.innerHTML = '';
    
    // Add appropriate media element based on type
    if (mediaType === 'image') {
        const img = document.createElement('img');
        img.src = mediaPath;
        img.className = 'max-w-full max-h-[80vh] object-contain';
        mediaContent.appendChild(img);
    } else if (mediaType === 'video') {
        const video = document.createElement('video');
        video.src = mediaPath;
        video.controls = true;
        video.autoplay = true;
        video.className = 'max-w-full max-h-[80vh]';
        mediaContent.appendChild(video);
    }
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Prevent page scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('mediaModal');
    modal.classList.add('hidden');
    
    // Re-enable page scrolling
    document.body.style.overflow = '';
    
    // Stop videos when closing modal
    const videoElements = document.querySelectorAll('#mediaContent video');
    videoElements.forEach(video => {
        video.pause();
    });
}

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
    // Function to update the clock and date
function updateClock() {
    const now = new Date();
    
    // Update date
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const dateString = now.toLocaleDateString('en-US', options);
    document.getElementById('current-date').textContent = dateString;
    
    // Update time
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    
    // Convert to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    
    // Add leading zeros
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    
    const timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    document.getElementById('current-time').textContent = timeString;
    
    // Update every second
    setTimeout(updateClock, 1000);
}

// Start the clock when the page loads
document.addEventListener('DOMContentLoaded', updateClock);
    
    // Start the clock when the page loads
    document.addEventListener('DOMContentLoaded', updateClock);
</script>
<!-- Media Modal -->
<div id="mediaModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-gray-700 to-black opacity-75" onclick="closeModal()"></div>
    <div class="bg-white rounded-lg p-2 max-w-3xl max-h-[90vh] relative shadow-2xl">
        <button onclick="closeModal()" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full p-1 shadow-lg hover:bg-red-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div id="mediaContent" class="w-full h-full flex items-center justify-center overflow-auto"></div>
    </div>
</div> 


