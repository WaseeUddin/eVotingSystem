<?php
include 'db.php';
header('Content-Type: application/json');

$today=date("Y-m-d H:i:s");
$sql="SELECT * FROM elections WHERE type='public' AND start_datetime<='$today' AND end_datetime>='$today' ORDER BY start_datetime DESC";
$result=$conn->query($sql);
$elections=[];
while($row=$result->fetch_assoc()){
  $election_id=$row['id'];
  $c_sql="SELECT c.id, c.name, c.party,
          (SELECT COUNT(*) FROM votes v WHERE v.candidate_id=c.id AND v.election_id=$election_id) as votes,
          (SELECT COALESCE(SUM(amount),0) FROM donations d WHERE d.candidate_id=c.id AND d.election_id=$election_id) as donations
          FROM candidates c WHERE c.election_id=$election_id";
  $c_result=$conn->query($c_sql);
  $candidates=[];
  while($c_row=$c_result->fetch_assoc()){ $candidates[]=$c_row; }
  $row['candidates']=$candidates;
  $elections[]=$row;
}
echo json_encode($elections);