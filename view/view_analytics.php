  <!-- https://seiyria.com/bootstrap-slider/  -->
  <input id="ex12c" type="text"/><br/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"  crossorigin="anonymous">
  <script>
      var sliderC = new Slider("#ex12c", { id: "slider12c", min: 0, max: 10, range: true, tooltip: 'always', value: [3, 7] });
  </script>
  <style>
    #slider12c .slider-selection {
      background: red;
    }
  </style>