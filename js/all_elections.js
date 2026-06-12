document.addEventListener("DOMContentLoaded", () => {

    const container = document.getElementById("electionList");

    fetch("backend/get_public_live_elections.php?t=" + Date.now())

    .then(res => res.json())

    .then(data => {

        if (!data || data.length === 0) {

            container.innerHTML = `
                <div class="election-card">
                    <h3>No Live Public Elections</h3>
                    <p>No public election is currently active.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = data.map(election => {

            const candidatesHtml = election.candidates

            .sort((a,b)=> Number(b.votes) - Number(a.votes))

            .map((c,index)=>{

                let medal = "";

                if(index === 0){
                    medal = "gold";
                }
                else if(index === 1){
                    medal = "silver";
                }
                else if(index === 2){
                    medal = "bronze";
                }

                return `
                    <div class="candidate-row ${medal}">

                        <div class="rank">
                            #${index + 1}
                        </div>

                        <div class="candidate-details">

                            <div class="candidate-name">
                                ${c.name}
                            </div>

                            <div class="party">
                                ${c.party}
                            </div>

                        </div>

                        <div class="stats">

                            <div class="vote-count">
                                ${c.votes} Votes
                            </div>

                            <div class="donation">
                                Donations: $${Number(c.total_raised).toLocaleString()}
                            </div>

                        </div>

                    </div>
                `;
            })

            .join("");

            return `

                <div class="election-card">

                    <h2>${election.title}</h2>

                    <p>${election.description}</p>

                    <div class="election-info">

                        <div class="info-box">
                            Total Candidates
                            <strong>${election.candidate_count}</strong>
                        </div>

                        <div class="info-box">
                            Total Votes
                            <strong>${election.vote_count}</strong>
                        </div>

                    </div>

                    <h3 class="ranking-title">
                        Live Candidate Ranking
                    </h3>

                    ${candidatesHtml}

                </div>

            `;

        }).join("");

    })

    .catch(error => {

        console.error(error);

        container.innerHTML = `
            <div class="election-card">
                <h3>Error Loading Elections</h3>
                <p>Please try again later.</p>
            </div>
        `;
    });

});