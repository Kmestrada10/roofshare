<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Property Listing</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lucide-react/0.263.0/lucide-react.min.js">
    <style>
        /* Styles for header from listing.css - Global * and body initially omitted */
        /* body {
          font-family: "Montserrat", sans-serif; 
          color: #333; 
          background-color: white; 
        } */

        .header {
          display: flex;
          align-items: center;
          padding: 0 20px;
          height: 80px;
          background-color: white; 
          width: 100%;
          border-bottom: 1px solid #eee;
          position: sticky;
          top: 0;
          z-index: 100;
        }

        .search-bar-container {
          display: flex;
          max-width: 480px;
          width: 30%;
          background-color: white;
          border-radius: 50px;
          overflow: hidden;
          height: 44px;
          border: 1px solid #ddd;
          position: absolute;
          left: 50%;
          top: 50%;
          transform: translate(-50%, -50%);
        }

        .search-input {
          flex-grow: 1;
          border: none;
          padding: 0 20px;
          font-size: 0.9rem;
          outline: none;
          color: #333; 
          height: 100%;
          background-color: white;
        }

        .search-input::placeholder {
          color: #888;
        }

        .search-button {
          background-color: white;
          color: #ff6600;
          border: none;
          padding: 0 20px;
          height: 100%;
          border-radius: 0 50px 50px 0;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1rem;
          transition: color 0.2s ease;
        }

        .search-button:hover {
          background-color: #f8f8f8;
          color: #e65c00;
        }

        .header-links {
          display: flex;
          gap: 25px;
          align-items: center;
          margin-left: auto;
        }

        .header-link {
          color: #333;
          text-decoration: none;
          font-weight: 500;
          font-size: 0.95rem;
          padding: 8px 12px;
          border-radius: 4px;
          transition: background-color 0.2s;
        }
        
        .custom-checkbox {
          appearance: none;
          -webkit-appearance: none;
          width: 20px;
          height: 20px;
          border: 2px solid #d1d5db;
          border-radius: 50%;
          position: relative;
          cursor: pointer;
          flex-shrink: 0;
        }
        
        .custom-checkbox:checked {
          background-color: #ea580c;
          border-color: #ea580c;
        }
        
        .custom-checkbox:checked::after {
          content: '×';
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: white;
          font-size: 16px;
          font-weight: bold;
        }

        /* Lucide Icons basic styling (actual icons are SVG) */
        .lucide {
            display: inline-block;
            vertical-align: middle;
            stroke-width: 2;
            fill: none;
            stroke: currentColor;
        }

        .cta-button {
          width: 100%;
          background-color: #ff6600;
          color: white;
          border: none;
          padding: 14px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          text-align: center;
          transition: background-color 0.2s ease;
        }

        .cta-button:hover {
          background-color: #e65c00;
        }

        .custom-orange-button {
          background-color: #fb923c; /* Tailwind orange-400 */
        }
        .custom-orange-button:hover {
          background-color: #f97316; /* Tailwind orange-500 for hover */
        }

        .force-square-shape {
            aspect-ratio: 1 / 1 !important;
            height: auto !important; /* Attempt to override any fixed height */
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-white" style="font-family: 'Montserrat', sans-serif;">
      <!-- Header from listing.php -->
      <header class="header">
        <div class="search-bar-container">
            <input
                class="search-input"
                type="text"
                placeholder="Enter an address, neighborhood, city, or ZIP code"
                aria-label="Search for properties"
            >
            <button class="search-button" aria-label="Submit search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
        </div>
        <div class="header-links">
            <a href="#" class="header-link">Manage Rentals</a>
            <a href="#" class="header-link">Sign In</a>
            <a href="#" class="header-link">Add Property</a>
        </div>
    </header>

      <!-- Main Content -->
      <main class="max-w-5xl mx-auto px-6 py-8" style="position: relative; z-index: 1;">
        <div class="space-y-8">
          <!-- Title Section -->
          <div>
            <h1 class="text-3xl font-semibold text-gray-900 mb-2">Create Your Property Listing</h1>
            <p class="text-gray-600">Add details about your property to start hosting</p>
          </div>
          
          <div class="bg-white">
            <!-- Property Information -->
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Property Title
                </label>
                <input
                  type="text"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                  placeholder="e.g., Luxury Downtown Loft with Mountain Views"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Property Description
                </label>
                <textarea
                  rows="6"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Describe your property, its features, and what makes it special..."
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Location
                </label>
                <input
                  type="text"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., Harpers Ferry, West Virginia"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Street Address
                </label>
                <input
                  type="text"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="123 Main Street"
                />
              </div>
            </div>

            <!-- Property Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-8">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Property Type
                </label>
                <select class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                  <option value="House">House</option>
                  <option value="Apartment">Apartment</option>
                  <option value="Condo">Condo</option>
                  <option value="Villa">Villa</option>
                  <option value="Cabin">Cabin</option>
                  <option value="Cottage">Cottage</option>
                  <option value="Loft">Loft</option>
                  <option value="Townhouse">Townhouse</option>
                  <option value="Bungalow">Bungalow</option>
                  <option value="Guest house">Guest house</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bed inline w-4 h-4 mr-1"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>
                  Bedrooms
                </label>
                <input
                  type="number"
                  min="0"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="2"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bath inline w-4 h-4 mr-1"><path d="m12 12-4-4"/><path d="M20 16V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11"/><path d="M10 16v4"/><path d="M14 16v4"/><path d="M21 16h-2a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2Z"/><path d="M3 16h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H3Z"/></svg>
                  Bathrooms
                </label>
                <input
                  type="number"
                  min="0"
                  step="0.5"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="2"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users inline w-4 h-4 mr-1"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  Max Guests
                </label>
                <input
                  type="number"
                  min="1"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="4"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Price per night (USD)
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-3 text-gray-400">$</span>
                  <input
                    type="number"
                    class="w-full pl-8 pr-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="199"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Host Name
                </label>
                <input
                  type="text"
                  class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., Sarah"
                />
              </div>
            </div>

            <!-- Photos Upload -->
            <div class="pt-8">
              <h2 class="text-xl font-medium text-gray-900 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera inline w-5 h-5 mr-2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                Property Photos
              </h2>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload mx-auto h-12 w-12 text-gray-400 mb-4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                <p class="text-sm text-gray-600">
                  Drag and drop your photos here or <span class="text-orange-600 cursor-pointer">browse files</span>
                </p>
                <p class="text-xs text-gray-400 mt-2">Maximum 10 photos, 5MB each</p>
              </div>
              
              <!-- Photo Preview Grid -->
              <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="force-square-shape bg-gray-100 rounded-lg border flex items-center justify-center">
                  <div class="text-center text-gray-400">
                     <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 mx-auto mb-2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    <span class="text-sm">Main Image</span>
                  </div>
                </div>
                <div class="force-square-shape bg-gray-100 rounded-lg border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 text-gray-400"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div class="force-square-shape bg-gray-100 rounded-lg border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 text-gray-400"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                <div class="force-square-shape bg-gray-100 rounded-lg border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 text-gray-400"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
                 <div class="force-square-shape bg-gray-100 rounded-lg border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera w-8 h-8 text-gray-400"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                </div>
              </div>
            </div>

            <!-- Amenities -->
            <div class="pt-8">
              <h2 class="text-xl font-medium text-gray-900 mb-4">What this place offers</h2>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-6 h-6"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                      <span class="text-gray-700">Mountain Views</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi w-6 h-6"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" x2="12.01" y1="20" y2="20"/></svg></div>
                      <span class="text-gray-700">Fast Wifi</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-droplet w-6 h-6"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg></div>
                      <span class="text-gray-700">Free Washer & Dryer</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wind w-6 h-6"><path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/><path d="M9.6 4.6A2 2 0 1 1 11 8H2"/><path d="M12.6 19.4A2 2 0 1 0 14 16H2"/></svg></div>
                      <span class="text-gray-700">Air conditioning</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart w-6 h-6"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.77-.77-.77a5.4 5.4 0 0 0-7.65 7.65l.77.77L12 21.23l7.65-7.65.77-.77a5.4 5.4 0 0 0 0-7.65z"/></svg></div>
                      <span class="text-gray-700">Gym</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car w-6 h-6"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><path d="M7 17h10"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg></div>
                      <span class="text-gray-700">Parking</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                 <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart w-6 h-6"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.77-.77-.77a5.4 5.4 0 0 0-7.65 7.65l.77.77L12 21.23l7.65-7.65.77-.77a5.4 5.4 0 0 0 0-7.65z"/></svg></div>
                      <span class="text-gray-700">Pets allowed</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
                 <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <div class="flex items-center flex-1">
                      <div class="text-gray-600 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-waves w-6 h-6"><path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/></svg></div>
                      <span class="text-gray-700">Pool</span>
                    </div>
                    <input type="checkbox" class="ml-auto custom-checkbox" />
                </label>
              </div>
            </div>

            <!-- Action Button -->
            <div class="pt-8">
              <button
                type="button"
                class="w-full custom-orange-button text-white py-3 px-6 rounded-lg font-medium transition duration-200"
              >
                Create Listing
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
</body>
</html>
