<?php
$candidates = $conn->query("SELECT id, name, party, total_raised FROM candidates");
while($row = $candidates->fetch_assoc()){
    echo '<div class="card" data-candidate-id="'.$row['id'].'">';
    echo '<h4>'.$row['name'].'</h4>';
    echo '<p>'.$row['party'].'</p>';
    echo '<div class="raised-box">';
    echo '<div class="raised-row"><span>Total Raised:</span> <strong>'.$row['total_raised'].'</strong></div>';
    echo '</div>';
    echo '<input type="number" class="donation-amount" placeholder="Enter amount">';
    echo '<div class="amounts">';
    echo '<button>$25</button><button>$50</button><button>$100</button><button>$250</button>';
    echo '</div>';
    echo '<button class="donate-btn">$ Donate</button>';
    echo '</div>';
}
?>
<script src="js/donation.js"></script>