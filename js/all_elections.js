document.addEventListener("DOMContentLoaded",()=>{
  const container=document.getElementById("electionList");
  fetch("backend/get_public_live_elections.php")
  .then(res=>res.json())
  .then(data=>{
    if(!data||data.length===0){
      container.innerHTML="<p>No live public elections at the moment.</p>"; return;
    }
    container.innerHTML=data.map(election=>`
      <div class="election-card">
        <h3>${election.title}</h3>
        <p>${election.description}</p>
        <p><b>Candidates:</b> ${election.candidates.length}</p>
        <div>${election.candidates.map(c=>`
          <p>${c.name} (${c.party}) - Votes: ${c.votes} - Donations: $${c.donations}</p>
        `).join('')}</div>
      </div>
    `).join('');
  })
  .catch(err=>{
    container.innerHTML="<p>Error loading elections.</p>";
    console.error(err);
  });
});