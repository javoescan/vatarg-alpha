<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VATARG Alpha</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/styles.css?v=<?=time()?>">
    <script src="./js/jquery-3.5.1.slim.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="title-container">
      <h2 class="text-center title">VATSIM Argentina Alpha System 2.0</h2>
    </div>
    <div class="firs-container">
      <div class="fir-container" id="saef">
        <button type="button" class="btn btn-primary" id="fir-saef">SAEF</button>
        <button type="button" class="btn btn-light" id="saez">SAEZ</button>
        <button type="button" class="btn btn-light" id="sabe">SABE</button>
        <button type="button" class="btn btn-light" id="sadf">SADF</button>
        <button type="button" class="btn btn-light" id="sadp">SADP</button>
        <button type="button" class="btn btn-light" id="sadm">SADM</button>
        <button type="button" class="btn btn-light" id="saar">SAAR</button>
        <button type="button" class="btn btn-light" id="sazm">SAZM</button>
        <button type="button" class="btn btn-light" id="sazn">SAZN</button>
        <button type="button" class="btn btn-light" id="sazs">SAZS</button>
        <button type="button" class="btn btn-light" id="sazr">SAZR</button>
        <button type="button" class="btn btn-light" id="sazy">SAZY</button>
        <button type="button" class="btn btn-light" id="saap">SAAP</button>
      </div>
      <div class="fir-container" id="sacf">
        <button type="button" class="btn btn-primary" id="fir-sacf">SACF</button>
        <button type="button" class="btn btn-light" id="SACO">SACO</button>
        <button type="button" class="btn btn-light" id="SANT">SANT</button>
        <button type="button" class="btn btn-light" id="SASA">SASA</button>
        <button type="button" class="btn btn-light" id="SANC">SANC</button>
        <button type="button" class="btn btn-light" id="SANE">SANE</button>
        <button type="button" class="btn btn-light" id="SANL">SANL</button>
        <button type="button" class="btn btn-light" id="SAOC">SAOC</button>
        <button type="button" class="btn btn-light" id="SAOV">SAOV</button>
      </div>
      <div class="fir-container" id="samf">
        <button type="button" class="btn btn-primary" id="fir-samf">SAMF</button>
        <button type="button" class="btn btn-light" id="SAME">SAME</button>
        <button type="button" class="btn btn-light" id="SANU">SANU</button>
        <button type="button" class="btn btn-light" id="SAMM">SAMM</button>
        <button type="button" class="btn btn-light" id="SAMR">SAMR</button>
        <button type="button" class="btn btn-light" id="SAOU">SAOU</button>
        <button type="button" class="btn btn-light" id="SAOR">SAOR</button>
      </div>
      <div class="fir-container" id="sarr">
        <button type="button" class="btn btn-primary" id="fir-sarr">SARR</button>
        <button type="button" class="btn btn-light" id="SARE">SARE</button>
        <button type="button" class="btn btn-light" id="SARP">SARP</button>
        <button type="button" class="btn btn-light" id="SARC">SARC</button>
        <button type="button" class="btn btn-light" id="SARI">SARI</button>
        <button type="button" class="btn btn-light" id="SARF">SARF</button>
      </div>
      <div class="fir-container" id="savf">
        <button type="button" class="btn btn-primary" id="fir-savf">SAVF</button>
        <button type="button" class="btn btn-light" id="SAVC">SAVC</button>
        <button type="button" class="btn btn-light" id="SAVV">SAVV</button>
        <button type="button" class="btn btn-light" id="SAVY">SAVY</button>
        <button type="button" class="btn btn-light" id="SAVT">SAVT</button>
        <button type="button" class="btn btn-light" id="SAVE">SAVE</button>
        <button type="button" class="btn btn-light" id="SAWC">SAWC</button>
        <button type="button" class="btn btn-light" id="SAWG">SAWG</button>
        <button type="button" class="btn btn-light" id="SAWE">SAWE</button>
        <button type="button" class="btn btn-light" id="SAWH">SAWH</button>
      </div>
    </div>
  </body>
</html>

<script>
  $("button").click(function() {
    if (this.id.includes("fir")) {
      var fir = this.id.replace("fir-", "");
      window.location.href = "/flights.php?fir=" + fir;
    } else {
      window.location.href = "/flights.php?fir=" + this.parentNode.id + "&airport=" + this.id;
    }
  });
</script>