<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - Document Attestation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/about.css">

  <!-- 🎨 Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="about-container">
    <h1 class="glow-title">About <span>Our Platform</span></h1>

    <!-- 🧠 Project Supervisor -->
    <div class="grid supervisor-section">
      <div class="supervisor-img">
        <img src="assets/images/malik.png" alt="Dr. Aamir Malik">
      </div>
      <div class="supervisor-info">
        <h2>Project Supervisor</h2>
        <h3>Dr. Aamir Malik</h3>
        <p>A visionary educator & leader, guiding innovation in digital transformation and AI-driven platforms across Pakistan’s academic frontier.</p>
      </div>
    </div>

    <!-- 💻 Dev Team -->
    <h2 class="dev-heading">Development Team</h2>
    <div class="grid flip-card-container">

      <!-- 🔄 Card 1 -->
      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-inner">
          <div class="flip-front">
            <img src="assets/images/Hassan.png" alt="Hassan Ali">
            <h3>Hassan Ali</h3>
          </div>
          <div class="flip-back">
            <h3>Hassan Ali</h3>
            <p>BS AI Student at Pak Austria</p>
            <p>Frontend & Backend Developer</p>
            <p>Built this platform from scratch.</p>
          </div>
        </div>
      </div>

      <!-- 🔄 Card 2 -->
      <div class="flip-card" onclick="this.classList.toggle('flipped')">
        <div class="flip-inner">
          <div class="flip-front">
            <img src="assets/images/gpt.png" alt="The Real OG">
            <h3>The Real OG</h3>
          </div>
          <div class="flip-back">
            <h3>The Real OG 🐐</h3>
            <p>The unseen legend</p>
            <p>Brains behind everything</p>
            <p>Runs on caffeine & chaos</p>
          </div>
        </div>
      </div>

    </div>

    <!-- 📝 About Text -->
    <div class="about-text">
      <p>This platform is built to solve a real problem in Pakistan — getting official documents attested without wasting time, money, or energy.</p>
      <p>Whether you're applying for a job abroad, getting transcripts verified, or just need official copies authenticated, we connect you to trusted workers who handle the attestation for you.</p>
      <p>Our mission is to digitize and simplify the entire document attestation process for every Pakistani citizen.</p>
    </div>

    <a href="index.php" class="back-link">⬅️ Back to Home</a>
  </div>
</body>
</html>
