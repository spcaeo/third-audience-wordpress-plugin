# Cache Browser Filters & Sorting - User Guide

## Quick Start

### Opening the Cache Browser
1. Log in to WordPress admin
2. Navigate to **Settings → Third Audience**
3. Click the **Cache Browser** menu item (or go to Settings → Cache Browser)

## Using Filters

### Opening the Filters Panel
Click the **Filters** button to expand/collapse the filters panel. When filters are active, you'll see a blue badge showing the count.

### Filter by Status
**Use Case**: Find only active or expired cache entries

**Steps**:
1. Open Filters panel
2. Select status from dropdown:
   - **All**: Show everything (default)
   - **Active**: Only valid, non-expired entries
   - **Expired**: Only entries past their expiration time
3. Click "Apply Filters"

**Example**: Finding expired entries to clean up
- Select "Expired"
- Click "Apply Filters"
- Use "Clear Expired" button to remove them all

### Filter by Size

#### Using Size Presets
**Use Case**: Quickly find small, medium, or large cached files

**Steps**:
1. Open Filters panel
2. Click a preset button:
   - **Small (<10KB)**: Files under 10,240 bytes
   - **Medium (10-50KB)**: Files 10,240 to 51,200 bytes
   - **Large (50-100KB)**: Files 50,200 to 102,400 bytes
3. Click "Apply Filters"

**Example**: Finding large cache entries consuming storage
- Click "Large (50-100KB)"
- Click "Apply Filters"
- Review large files and delete if needed

#### Using Custom Size Range
**Use Case**: Find entries within a specific size range

**Steps**:
1. Open Filters panel
2. Enter **Min (bytes)**: Minimum file size (e.g., 20000)
3. Enter **Max (bytes)**: Maximum file size (e.g., 30000)
4. Click "Apply Filters"

**Tips**:
- You can set only Min or only Max
- 1 KB = 1,024 bytes
- 1 MB = 1,048,576 bytes

### Filter by Date

#### Using Date Presets
**Use Case**: Quickly find recently cached entries

**Steps**:
1. Open Filters panel
2. Click a preset button:
   - **Last 24 Hours**: Entries created in the past day
   - **Last 7 Days**: Entries created in the past week
   - **Last 30 Days**: Entries created in the past month
3. Click "Apply Filters"

**Example**: Finding today's cached entries
- Click "Last 24 Hours"
- Click "Apply Filters"

#### Using Custom Date Range
**Use Case**: Find entries created in a specific time period

**Steps**:
1. Open Filters panel
2. Click **From** date field and select start date
3. Click **To** date field and select end date
4. Click "Apply Filters"

**Tips**:
- You can set only From or only To
- Dates are inclusive (entire day included)
- Use your browser's date picker for easy selection

### Combining Filters
**Use Case**: Narrow down to very specific entries

**Example**: Find large, active entries from last week
1. Status: **Active**
2. Size: **Large (50-100KB)**
3. Date: **Last 7 Days**
4. Click "Apply Filters"

**Result**: Only active cache entries between 50-100KB created in the past 7 days

### Clearing Filters
**To remove all filters**:
1. Click "Clear Filters" button in the Filters panel
2. This resets the page to show all entries with default sorting

## Sorting Entries

### How to Sort
Click any column header to sort by that column:
- **URL**: Alphabetical order
- **Size**: Numerical order (bytes)
- **Expires**: Chronological order (timestamp)

### Toggle Sort Direction
- **First click**: Sort descending (Z-A, largest-smallest, newest-oldest)
- **Second click**: Sort ascending (A-Z, smallest-largest, oldest-newest)

### Visual Indicators
- **Active column**: Highlighted with blue arrow
- **Arrow up**: Ascending order
- **Arrow down**: Descending order

### Default Sort
By default, entries are sorted by **Expiration** in **descending** order (newest entries first).

## Common Use Cases

### Use Case 1: Clean Up Old Expired Entries
**Goal**: Remove all expired cache to free up space

**Steps**:
1. Open Filters
2. Status: **Expired**
3. Apply Filters
4. Click "Clear Expired" button in bulk actions
5. Confirm deletion

### Use Case 2: Find Large Files
**Goal**: Identify cache entries consuming the most storage

**Steps**:
1. Click **Size** header to sort by size
2. Click again if needed to get largest first (descending)
3. Review large entries
4. Optionally filter by size: Large (50-100KB)

### Use Case 3: Audit Recent Cache Activity
**Goal**: See what was cached today

**Steps**:
1. Open Filters
2. Date: **Last 24 Hours**
3. Apply Filters
4. Sort by: **Expires** (descending)

### Use Case 4: Find Specific URL
**Goal**: Locate cache entries for a specific page

**Steps**:
1. Use the **Search URL** field (not in Filters panel)
2. Enter part of the URL (e.g., "blog" or "contact")
3. Click **Search**
4. Optionally combine with filters

### Use Case 5: Prepare for Cache Warmup
**Goal**: Identify posts that aren't cached

**Steps**:
1. Check "Uncached Posts" count in Cache Warmup section
2. Filter by Status: **Active** to see what IS cached
3. Click "Warm All Cache" to cache everything

## Bookmarking Filtered Views

### Save Your View
When you apply filters and sorting, the URL updates with all parameters. You can:
- **Bookmark** the URL to save this specific view
- **Share** the URL with other admins
- **Reload** the page and filters persist

**Example URL**:
```
/wp-admin/options-general.php?page=third-audience-cache-browser
&status=active
&size_min=10240
&size_max=51200
&date_from=2026-01-15
&date_to=2026-01-21
&orderby=size
&order=DESC
```

This URL shows: Active entries, 10-50KB size, from Jan 15-21, sorted by size descending.

## Tips & Tricks

### Tip 1: Start Broad, Then Narrow
1. Start with just one filter (e.g., Status: Active)
2. Review results
3. Add more filters as needed

### Tip 2: Use Presets for Speed
Quick presets are faster than typing custom values:
- Size presets: One click
- Date presets: One click
- Then customize if needed

### Tip 3: Sort After Filtering
1. Apply your filters first
2. Then sort the filtered results
3. This gives you the most relevant view

### Tip 4: Check the Filter Badge
The blue badge number tells you how many filters are active at a glance.

### Tip 5: Combine with Search
You can use the Search field AND filters together:
1. Search for "blog"
2. Apply filters (e.g., Last 7 Days)
3. Get blog posts cached in the last week

## Keyboard Shortcuts

- **Tab**: Navigate between filter inputs
- **Enter**: Submit filters (when focused in a filter field)
- **Esc**: Close filter panel (when focused)

## Mobile Usage

On mobile devices:
- Filters stack vertically
- Preset buttons expand to full width
- Filter panel scrolls if needed
- All functionality preserved

## Troubleshooting

### "No cache entries found"
**Possible causes**:
1. Filters too restrictive - try broadening them
2. No entries match criteria - try different filters
3. Cache is empty - run Cache Warmup

**Solution**: Click "Clear Filters" and start over

### Filters not working
**Possible causes**:
1. Forgot to click "Apply Filters"
2. Date format incorrect
3. Size values in wrong units

**Solution**:
- Always click "Apply Filters" after changing values
- Use date picker instead of typing dates
- Use bytes for size (1 KB = 1024 bytes)

### Filter badge shows wrong count
**Possible causes**:
1. Empty/zero values are ignored (they don't count)
2. "All" status doesn't count as a filter

**Expected behavior**:
- Empty fields don't count
- Default values don't count
- Only active, non-default filters count

## Advanced Techniques

### Technique 1: Progressive Filtering
Start with one dimension, then add others:
1. Filter by Date: Last 30 Days
2. Check results
3. Add Size: Large
4. Check results
5. Add Status: Active
6. Final refined results

### Technique 2: Exclusion via Sort
To find smallest/oldest entries:
1. Sort by Size (ascending)
2. Or sort by Expiration (ascending)
3. No need for filters

### Technique 3: Audit Workflow
Monthly cache audit routine:
1. Filter: Last 30 Days
2. Sort by: Size (descending)
3. Review large entries
4. Filter: Expired
5. Clear expired entries
6. Run Cache Warmup

## Need Help?

- **Documentation**: See CACHE_BROWSER_FILTERS_IMPLEMENTATION.md
- **Support**: Contact Third Audience support
- **Issues**: Report bugs on GitHub

## Version
This guide applies to Third Audience v2.1.0 and later.
