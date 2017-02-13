function JSONGet(url, func) {
  let request = new XMLHttpRequest()
  request.open('GET', url, true)
  request.onload = function() {
    if (request.status == 200 || request.status == 304) {
      let data = JSON.parse(request.responseText)
      func(data)
    }
  };
  request.send()
}

document.addEventListener("DOMContentLoaded", function(event) {
  JSONGet("/ParaJSON.php", (dump_in) => {
    let dump = dump_in

    let header = document.getElementsByClassName("header")[0]
    let server_name = document.createElement("span")
    server_name.appendChild(document.createTextNode(dump.info.sv_hostname))
    server_name.className = "server_name"
    header.appendChild(server_name)

    let players = document.getElementsByClassName("players")[0]
    dump.players.forEach((plyr) => {
      let player_div = document.createElement("div")
      player_div.className = "player_div"

      let player_name = document.createElement("span")
      player_name.className = "player_name"
      player_name.appendChild(document.createTextNode(plyr.name))
      player_div.appendChild(player_name)

      let player_score = document.createElement("span")
      player_score.className = "player_score"
      player_score.appendChild(document.createTextNode(plyr.score))
      player_div.appendChild(player_score)

      players.appendChild(player_div)
    })
  })
});
