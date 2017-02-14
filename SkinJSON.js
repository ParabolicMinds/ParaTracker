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

function GetHexFromColorNum(num) {
  switch (num) {
    case '0':
    return "#000"
    case '1':
    return "#F00"
    case '2':
    return "#0F0"
    case '3':
    return "#FF0"
    case '4':
    return "#00F"
    case '5':
    return "#0FF"
    case '6':
    return "#F0F"
    case '7':
    return "#EEE"
    case '8':
    return "#F40"
    case '9':
    return "#888"
  }
  return null
}

function GetColoredStringDiv(str) {
  let str_seq = document.createElement("div")
  str_seq.style.display = "inline-block"
  str_seq.style.flex = "1 1 auto"

  let str_sub = ""
  let current_color = "#EEE"
  let str_exp = str.split('')

  for (let i = 0; i < str_exp.length; i++) {
    if (str_exp[i] == "^") {
      let color = GetHexFromColorNum(str_exp[i+1])
      if (color == null) {
        str_sub += str_exp[i]
        continue
      }
      if (str_sub.length > 0) {
        let new_span = document.createElement("span")
        new_span.className = "player_name"
        if (current_color != null) {
          new_span.style.color = current_color
        }
        new_span.appendChild(document.createTextNode(str_sub))
        str_seq.appendChild(new_span)
        str_sub = ""
      }
      current_color = color
      i++
    } else {
      str_sub += str_exp[i]
    }
  }

  let new_span = document.createElement("span")
  if (current_color != null) {
    new_span.style.color = current_color
  }
  new_span.appendChild(document.createTextNode(str_sub))
  str_seq.appendChild(new_span)

  return str_seq
}

document.addEventListener("DOMContentLoaded", function(event) {
  JSONGet("/ParaJSON.php", (dump_in) => {
    let dump = dump_in

    let header = document.getElementsByClassName("header")[0]
    let server_name = GetColoredStringDiv(dump.info.sv_hostname)
    server_name.className = "server_name"
    header.appendChild(server_name)

    let players = document.getElementsByClassName("players")[0]
    dump.players.forEach((plyr) => {
      let player_div = document.createElement("div")
      player_div.className = "player_div"

      let player_name = GetColoredStringDiv(plyr.name)
      player_div.appendChild(player_name)

      let player_score = document.createElement("span")
      player_score.className = "player_score"
      player_score.appendChild(document.createTextNode(plyr.score))
      player_div.appendChild(player_score)

      players.appendChild(player_div)
    })
  })
});
