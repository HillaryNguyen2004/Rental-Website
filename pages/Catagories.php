<section class="menu" id="menu">
    <h1 class="heading"> Available <span>Rentals</span></h1>

    <!-- Search Bar -->
    <input type="text" id="search" placeholder="Search Properties..." style="width: 100%; padding: 10px; margin-bottom: 20px;">

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

        function loaddata(query = "") {
            $.ajax({
                url: 'pages/load-data.php',  // Make sure this path is correct!
                method: 'POST',
                data: { limit: limit, offset: offset, query: query },
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
            $("#pageNumber").text(currentPage);
            loaddata(query);
        });

        // Next page
        $("#nextPage").click(function () {
            offset += limit;
            currentPage++;
            $("#pageNumber").text(currentPage);
            loaddata();
        });

        // Previous page
        $("#prevPage").click(function () {
            if (offset > 0) {
                offset -= limit;
                currentPage--;
                $("#pageNumber").text(currentPage);
                loaddata();
            }
        });
    });
</script>
