<section class="menu" id="menu">
    <h1 class="heading"> Available <span>Rentals</span></h1>

    <!-- Search Bar -->
    <input type="text" id="search" placeholder="Search Properties..." style="width: 100%; padding: 10px; margin-bottom: 10px;">

    <!-- Sort Button -->
    <button id="sortButton" class="btn" style="margin-bottom: 20px;">Sort by Title (A-Z)</button>

    <div class="box-container" id="result">
        <!-- Data will be loaded here via AJAX -->
    </div>

    <!-- Pagination Buttons -->
    <div id="pagination">
        <button id="prevPage" class="btn">Previous</button>
        <span id="pageNumber">1</span>
        <button id="nextPage" class="btn">Next</button>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        var limit = 6; // Items per page
        var offset = 0; // Starting offset
        var currentPage = 1;
        var sort = ""; // Sorting state (empty, title_asc, title_desc)

        function loaddata(query = "", sortParam = sort) {
            $.ajax({
                url: 'pages/load-data.php',  // Ensure this path matches your file structure
                method: 'POST',
                data: { limit: limit, offset: offset, query: query, sort: sortParam },
                success: function (result) {
                    $("#result").html(result);
                }
            });
        }

        loaddata(); // Load first batch

        // Search functionality
        $("#search").on("keyup", function () {
            let query = $(this).val();
            offset = 0;
            currentPage = 1;
            sort = ""; // Reset sort when searching
            $("#sortButton").text("Sort by Title (A-Z)"); // Reset button text
            $("#pageNumber").text(currentPage);
            loaddata(query);
        });

        // Sort functionality with toggle
        $("#sortButton").click(function () {
            if (sort === "" || sort === "title_desc") {
                sort = "title_asc";
                $(this).text("Sort by Title (Z-A)");
            } else {
                sort = "title_desc";
                $(this).text("Sort by Title (A-Z)");
            }
            offset = 0; // Reset to first page
            currentPage = 1;
            $("#pageNumber").text(currentPage);
            loaddata($("#search").val(), sort); // Pass current search query and sort
        });

        // Next page
        $("#nextPage").click(function () {
            offset += limit;
            currentPage++;
            $("#pageNumber").text(currentPage);
            loaddata($("#search").val(), sort); // Maintain search and sort
        });

        // Previous page
        $("#prevPage").click(function () {
            if (offset > 0) {
                offset -= limit;
                currentPage--;
                $("#pageNumber").text(currentPage);
                loaddata($("#search").val(), sort); // Maintain search and sort
            }
        });
    });
</script>