const params = new URLSearchParams(window.location.search);

const electionId = params.get("id");

fetch(`backend/get_election_details.php?id=${electionId}`)

.then(res => res.json())

.then(data => {

    if (data.status !== "success") {

        document.body.innerHTML = "<h2>Election Not Found</h2>";

        return;
    }

    const election = data.election;

    document.getElementById("electionInfo").innerHTML = `

        <div class="header">

            <h1>${election.title}</h1>

            <p>${election.description}</p>

            <p>
                <strong>Start:</strong>
                ${election.start_datetime}
            </p>

            <p>
                <strong>End:</strong>
                ${election.end_datetime}
            </p>

        </div>

    `;

    let html = "";

    data.candidates.forEach(candidate => {

        html += `

            <div class="card">

                <h2>${candidate.name}</h2>

                <p>
                    <strong>Party:</strong>
                    ${candidate.party}
                </p>

                <p>
                    ${candidate.campaign_statement}
                </p>

                <p class="vote">
                    Votes: ${candidate.vote_count}
                </p>

                <p class="donation">
                    Donations: ৳${candidate.donation_total}
                </p>

            </div>

        `;
    });

    document.getElementById("candidateList").innerHTML = html;

})

.catch(err => {

    console.error(err);

});