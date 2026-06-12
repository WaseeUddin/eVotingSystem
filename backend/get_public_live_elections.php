<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

header('Content-Type: application/json');

$today = date("Y-m-d H:i:s");

$elections = [];

$sql = "
SELECT *
FROM elections
WHERE election_type = 'public'
AND start_datetime <= '$today'
AND end_datetime >= '$today'
ORDER BY start_datetime DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $electionId = (int)$row['id'];

        $candidateCount = 0;
        $voteCount = 0;

        $candidateQuery = $conn->query("
            SELECT COUNT(*) total
            FROM election_candidates
            WHERE election_id = $electionId
        ");

        if ($candidateQuery && $candidateRow = $candidateQuery->fetch_assoc()) {
            $candidateCount = (int)$candidateRow['total'];
        }

        $voteQuery = $conn->query("
            SELECT COUNT(*) total
            FROM votes
            WHERE election_id = $electionId
        ");

        if ($voteQuery && $voteRow = $voteQuery->fetch_assoc()) {
            $voteCount = (int)$voteRow['total'];
        }

        $candidates = [];

        $candidateResult = $conn->query("
            SELECT
                c.id,
                c.name,
                c.party,
                c.total_raised,

                (
                    SELECT COUNT(*)
                    FROM votes v
                    WHERE v.candidate_id = c.id
                    AND v.election_id = $electionId
                ) AS votes

            FROM election_candidates ec
            INNER JOIN candidates c
                ON c.id = ec.candidate_id
            WHERE ec.election_id = $electionId
        ");

        if ($candidateResult) {
            while ($candidate = $candidateResult->fetch_assoc()) {
                $candidates[] = $candidate;
            }
        }

        $row['candidate_count'] = $candidateCount;
        $row['vote_count'] = $voteCount;
        $row['candidates'] = $candidates;

        $elections[] = $row;
    }
}

echo json_encode($elections);

$conn->close();
?>