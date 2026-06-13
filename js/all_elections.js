document.addEventListener("DOMContentLoaded", function () {

    const container = document.getElementById("electionList");

    fetch("backend/get_public_live_elections.php?t=" + Date.now())

    .then(response => response.json())

    .then(data => {

        if (
            data.status !== "success" ||
            !data.elections ||
            data.elections.length === 0
        ) {

            container.innerHTML = `
                <div class="election-card">
                    <h3>No Public Elections Found</h3>
                </div>
            `;

            return;
        }

        let html = "";

        data.elections.forEach(election => {

            html += `

                <div class="election-card">

                    <h2>${election.title}</h2>

                    <p>${election.description}</p>

                    <div class="stats">

                        <div class="stat-box">
                            <span>Status</span>
                            <strong>${election.status}</strong>
                        </div>

                        <div class="stat-box">
                            <span>Type</span>
                            <strong>${election.election_type}</strong>
                        </div>

                    </div>

                    <p>
                        <strong>Start:</strong>
                        ${election.start_datetime}
                    </p>

                    <p>
                        <strong>End:</strong>
                        ${election.end_datetime}
                    </p>

                    <a
                        href="election_details.html?id=${election.id}"
                        class="view-btn"
                    >
                        View Live Results
                    </a>

                </div>

            `;
        });

        container.innerHTML = html;

    })

    .catch(error => {

        console.error(error);

        container.innerHTML = `
            <div class="election-card">
                <h3>Failed To Load Elections</h3>
            </div>
        `;

    });

});