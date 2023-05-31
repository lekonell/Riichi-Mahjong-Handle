$(document).ready(function() {
    const mode_light = 0;
    const mode_dark = 1;

    const wind_east = 0;
    const wind_south = 1;
    const wind_west = 2;
    const wind_north = 3;

    const handle_tsumo = 0;
    const handle_ron = 1;

    String.prototype.endsWith = function(str) {
        var len = this.length;

        if (len < str.length) return false;
        for (var i = 0; i < str.length; i++) {
            if (this[len - str.length + i] != str[i]) {
                return false;
            }
        }

        return true;
    };

    const StorageManager = {
        get: function(name) {
            if (localStorage.getItem(name) !== null) return JSON.parse(localStorage.getItem(name)).value;
        },
        set: function(name, value) {
            return localStorage.setItem(name, JSON.stringify({value}));
        },
        clear: function() {
            return localStorage.clear();
        }
    }

    const game = {
        option: {
            'light_mode': mode_light,
            'guess_depth': 6,
            'round_wind': wind_east,
            'round_level': new Date().getDate() % 4 + 1,
            'seat_wind': wind_east,
            'handle_type': handle_tsumo,
            'guessed_depth': 0,
            'tiles_placed': 0,
            'solution': '',
            'end': false
        },
        callback: {
            'flag': false,
            'candidates': {
                'head': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                'body': {
                    'sets': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                    'sequences': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                },
            },
            'dialog_timer': false,
            'hand_dice': 0,
            'load_from_code': false
        },
        guesses: [],
        statistics: [0, 0, 0, 0, 0, 0, 0],
        streaks: {
            best_streak: 0,
            current_streak: 0
        },
        presses: '',
        url: 'https://mahjong.modaweb.kr/',
        init: function() {
            game.initGuess();
            game.initHand();
            game.loadGame();
            game.setStage();

            if (game.guesses.length > 0) {
                var solve_cnt = 0;

                for (var i = 0; i < 14; i++) {
                    if (game.guesses[game.guesses.length - 1][i] == game.option.solution[i]) solve_cnt += 1;
                }

                if (solve_cnt >= 14) {
                    game.option.end = true;
                    game.showStatistics();
                }
            }
        },
        initCallback: function() {
            game.callback.flag = false;
            game.callback.candidates = {
                'head': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                'body': {
                    'sets': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                    'sequences': [[0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0, 0, 0, 0]],
                },
            };
        },
        newGame: function() {
            if (game.guesses.length > 0 && !game.option.end) {
                game.streaks.current_streak = 0;
                game.statistics[game.option.guess_depth] += 1;
            }

            game.initGuess();
            game.initHand();

            game.guesses = [];
            game.option.guessed_depth = 0;
            game.option.tiles_placed = 0;
            game.option.round_wind = Math.floor(Math.random() * 276) % 4;
            game.option.round_level = new Date().getDate() % 4 + 1;
            game.option.seat_wind = Math.floor(Math.random() * 1104) % 4;
            game.option.handle_type = Math.floor(Math.random() * 2272) % 2;
            game.option.solution = '';
            game.option.end = false;

            game.hideAnswer();
            game.setStage();
            game.saveGame();
        },
        shareGame: function() {
            var gameStatus = '';

            var tile_table = [];
            var guess_table = [];
            for (var i = 0; i < game.table.Tiles.length; i++) {
                tile_table[game.table.Tiles[i]] = 0;
                guess_table[game.table.Tiles[i]] = 0;
            }

            for (var i = 0; i < 14; i++) {
                tile_table[game.option.solution[i]] += 1;
            }

            for (var i = 0; i < game.guesses.length; i++) {
                for (var j = 0; j < game.table.Tiles.length; j++) {
                    guess_table[game.table.Tiles[j]] = 0;
                }

                for (var j = 0; j < 14; j++) {
                    if (game.guesses[i][j] == game.option.solution[j]) {
                        guess_table[game.guesses[i][j]] += 1;
                    }
                }

                for (var j = 0; j < 14; j++) {
                    if (game.guesses[i][j] == game.option.solution[j]) {
                        gameStatus += '🟦';
                    }
                    else if (game.guesses[i][j] != game.option.solution[j]) {
                        guess_table[game.guesses[i][j]] += 1;

                        if (tile_table[game.guesses[i][j]] - guess_table[game.guesses[i][j]] >= 0) {
                            gameStatus += '🟧';
                        }
                        else {
                            gameStatus += '⬛';
                        }
                    }
                }

                gameStatus += '\n';
            }

            game.evaluateSolution(game.option.solution);

            var game_code = '#';
            var candidatesCopied = game.hardcopy(game.callback.candidates);
            var head_idx = 0;
            for (var i = 0; i < 4; i++) {
                if (candidatesCopied.head[i].indexOf(1) != -1) {
                    head_idx = candidatesCopied.head[i].indexOf(1) + 9 * i;
                }
            }

            game_code += game.table.encode_code[head_idx + 16];

            for (var i = 0; i < 4; i++) {
                for (var j = 0; j < 9; j++) {
                    if (candidatesCopied.body.sets[i][j] != 0) {
                        candidatesCopied.body.sets[i][j] -= 1;
                        game_code += game.table.encode_code[i * 9 + j + 21];
                        j -= 1;
                        continue;
                    }

                    if (candidatesCopied.body.sequences[i][j] != 0) {
                        candidatesCopied.body.sequences[i][j] -= 1;
                        game_code += game.table.encode_code[i * 7 + j];
                        j -= 1;
                    }
                }
            }

            game_code += game.table.encode_code[game.option.hand_dice + 32];

            var option_code = 0;
            option_code = option_code | (game.option.round_wind << 3);
            option_code = option_code | (game.option.seat_wind << 1);
            option_code = option_code | game.option.handle_type;
            
            game_code += game.table.encode_code[option_code + 16];

            function showTooltip(message) {
                $('#statistics-share-tooltip').addClass('show');
                $('#statistics-share-tooltip').find('p.context').text(message);
                var tooltipLeft = $('#statistics-share-game').offset().left + $('#statistics-share-game').innerWidth() / 2 - $('#statistics-share-tooltip').innerWidth() / 2;
                var tooltipTop = $('#statistics-share-game').offset().top - $('#statistics-share-tooltip').height() - 18;
                $('#statistics-share-tooltip').css('top', `${tooltipTop}px`);
                $('#statistics-share-tooltip').css('left', `${tooltipLeft}px`);
                $('#statistics-share-tooltip').find('div.tooltip-arrow').css('left', `${$('#statistics-share-tooltip').width() / 2 - 6}px`);
                $('#statistics-share-tooltip').stop().animate({
                    opacity: 1
                }, 100);

                $('#statistics-share-tooltip').animate({
                    opacity: 1
                }, 500, function() {
                    $('#statistics-share-tooltip').animate({
                        opacity: 0
                    }, 500, function() {
                        $('#statistics-share-tooltip').removeClass('show');
                    });
                });
            }

            var shareTag = `Riichi Mahjong Handle ${game_code} ${game.guesses.length}/${game.option.guess_depth}\n${gameStatus}\nTry at ${game.url}${game_code}`;

            var isIE = false || !!document.documentMode;
            if (isIE) {
                window.clipboardData.setData("text", shareTag);
                showTooltip('복사 성공!');
            }
            else {
                navigator.clipboard.writeText(shareTag).then(function() {
                    showTooltip('복사 성공!');
                }, function() {
                    showTooltip('복사 권한을 먼저 허용해주세요.');
                });
            }

            console.dir(shareTag);
        },
        saveGame: function() {
            StorageManager.set("option", game.option);
            StorageManager.set("guesses", game.guesses);
            StorageManager.set("streaks", game.streaks);
            StorageManager.set("statistics", game.statistics);
        },
        loadGame: function() {
            if (!StorageManager.get("option")) StorageManager.set("option", game.option);
            if (!StorageManager.get("statistics")) StorageManager.set("statistics", game.statistics);
            if (!StorageManager.get("streaks")) StorageManager.set("streaks", game.streaks);
            if (!StorageManager.get("guesses")) StorageManager.set("guesses", game.guesses);

            game.statistics = StorageManager.get("statistics");

            if (game.statistics.length <= game.option.guess_depth) {
                game.statistics[game.statistics.length] = 0;
            }

            game.streaks = StorageManager.get("streaks");

            if (location.href.indexOf('#') != -1) {
                var game_code = location.href.split('#')[1];
                if (game_code.length != 7) location.replace(game.url);

                var code_idx = [0, 0, 0, 0, 0, 0, 0];
                for (var i = 0; i < code_idx.length; i++) {
                    code_idx[i] = game.table.encode_code.indexOf(game_code[i]);
                }

                var restored_sol = [];
                restored_sol.push(game.table.Tiles[code_idx[0] - 16]);
                restored_sol.push(game.table.Tiles[code_idx[0] - 16]);

                for (var i = 1; i < 5; i++) {
                    if (code_idx[i] >= 21) {
                        restored_sol.push(game.table.Tiles[code_idx[i] - 21]);
                        restored_sol.push(game.table.Tiles[code_idx[i] - 21]);
                        restored_sol.push(game.table.Tiles[code_idx[i] - 21]);
                    }
                    else {
                        restored_sol.push(game.table.Tiles[code_idx[i]]);
                        restored_sol.push(game.table.Tiles[code_idx[i] + 1]);
                        restored_sol.push(game.table.Tiles[code_idx[i] + 2]);
                    }
                }
                
                solutionSorted = game.sortSolution(restored_sol);
                var dice = code_idx[5] - 32;
                var tmp = solutionSorted[dice];
                    
                for (var i = dice; i < 13; i++) {
                    solutionSorted[i] = solutionSorted[i + 1];
                }

                solutionSorted[13] = tmp;
                game.option.solution = solutionSorted;

                var option_code = code_idx[6] - 16;
                game.option.round_wind = (option_code & (3 << 3)) >> 3;
                game.option.seat_wind = (option_code & (3 << 1)) >> 1;
                game.option.handle_type = (option_code & 1);
                game.option.hand_dice = dice;
                
                game.callback.load_from_code = true;
            }

            var options = StorageManager.get("option");
            game.option.light_mode = options["light_mode"];
            if (!game.callback.load_from_code) game.option.round_wind = options.round_wind;
            game.option.round_level = options.round_level;
            if (!game.callback.load_from_code) game.option.seat_wind = options.seat_wind;
            if (!game.callback.load_from_code) game.option.guessed_depth = options.guessed_depth;
            if (!game.callback.load_from_code) game.option.solution = options.solution;
            if (!game.callback.load_from_code) game.option.hand_dice = options.hand_dice;

            game.setLight();

            if (!game.callback.load_from_code) {
                game.guesses = StorageManager.get("guesses");
                for (var i = 0; i < game.guesses.length; i++) {
                    var handle = $(`#handle-depth-${i + 1}`);
                    for (var j = 0; j < 14; j++) {
                        var placer = $(handle).find(`div.handle-item-${j + 1}`);

                        var placer_script = `<img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/${game.guesses[i][j]}.svg" alt="${game.guesses[i][j]}">`;
                        placer_script += `<img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/${game.guesses[i][j]}.svg" alt="${game.guesses[i][j]}">`;
                        
                        $(placer).html(placer_script);
                    }

                    game.highlightGuess(i + 1);
                }

                game.option.guessed_depth = game.guesses.length;
            }

            if (game.callback.load_from_code) {
                game.evaluateSolution(game.option.solution);
            }
        },
        initGuess: function() {
            var handle_guess_element = '';
            for (var i = 0; i < game.option.guess_depth; i++) {
                handle_guess_element += `<div id="handle-depth-${i + 1}" class="flex justify-center mb-1">`;
                for (var j = 0; j < 14; j++) {
                    handle_guess_element += `<div class="handle-item handle-item-${j + 1} last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white bg-white dark:bg-slate-900 last:border-lime-300 border-slate-200 last:dark:border-lime-600 dark:border-slate-600"></div>`;
                }
                handle_guess_element += '</div>';
            }
            $('#handle-guess').html(handle_guess_element);

            var guesses = StorageManager.get("guesses");
        },
        highlightGuess: function(line) {
            var handle = $(`#handle-depth-${line}`);
            var user_sol = [];

            var tile_table = [];
            var user_tile_table = [];
            for (var i = 0; i < game.table.Tiles.length; i++) {
                tile_table[game.table.Tiles[i]] = 0;
                user_tile_table[game.table.Tiles[i]] = 0;
            }

            for (var i = 0; i < 14; i++) {
                tile_table[game.option.solution[i]] += 1;
            }

            for (var i = 0; i < 14; i++) {
                var placer = $(handle).find(`div.handle-item-${i + 1}`);
                user_sol[i] = $(placer).find('img').attr('src').split('.svg')[0].split('light/')[1];

                if (user_sol[i] == game.option.solution[i]) {
                    user_tile_table[user_sol[i]] += 1;

                    $(placer).removeClass('bg-white');
                    $(placer).removeClass('border-slate-200');
                    $(placer).removeClass('dark:bg-slate-900');
                    $(placer).addClass('bg-blue-500');
                    $(placer).addClass('border-blue-500');

                    var handle_trace = $(`button[data-tile=${user_sol[i]}]`);
                    $(handle_trace).removeClass('light:bg-slate-200');
                    $(handle_trace).removeClass('light:hover:bg-slate-300');
                    $(handle_trace).removeClass('light:active:bg-slate-400');
                    $(handle_trace).removeClass('dark:bg-slate-600');
                    $(handle_trace).removeClass('dark:hover:bg-slate-700');
                    $(handle_trace).removeClass('dark:active:bg-slate-800');
                    $(handle_trace).removeClass('bg-slate-400');
                    $(handle_trace).removeClass('bg-orange-500');
                    $(handle_trace).removeClass('light:hover:bg-orange-600');
                    $(handle_trace).removeClass('light:active:bg-orange-700');
                    $(handle_trace).addClass('bg-blue-500');
                    $(handle_trace).addClass('hover:bg-blue-600');
                    $(handle_trace).addClass('active:bg-blue-700');
                }
            }

            for (var i = 0; i < 14; i++) {
                var placer = $(handle).find(`div.handle-item-${i + 1}`);
                user_sol[i] = $(placer).find('img').attr('src').split('.svg')[0].split('light/')[1];

                if (user_sol[i] != game.option.solution[i]) {
                    user_tile_table[user_sol[i]] += 1;

                    if (tile_table[user_sol[i]] - user_tile_table[user_sol[i]] >= 0) {
                        $(placer).removeClass('bg-white');
                        $(placer).removeClass('border-slate-200');
                        $(placer).removeClass('dark:bg-slate-900');
                        $(placer).addClass('bg-orange-500');
                        $(placer).addClass('border-orange-500');
                        $(placer).addClass('dark:bg-orange-700');
                        $(placer).addClass('dark:border-orange-700');

                        var handle_trace = $(`button[data-tile=${user_sol[i]}]`);
                        $(handle_trace).removeClass('light:bg-slate-200');
                        $(handle_trace).removeClass('light:hover:bg-slate-300');
                        $(handle_trace).removeClass('light:active:bg-slate-400');
                        $(handle_trace).removeClass('dark:bg-slate-600');
                        $(handle_trace).removeClass('dark:hover:bg-slate-700');
                        $(handle_trace).removeClass('dark:active:bg-slate-800');
                        $(handle_trace).removeClass('bg-slate-400');

                        if (!$(handle_trace).hasClass('bg-blue-500')) {
                            $(handle_trace).addClass('bg-orange-500');
                            $(handle_trace).addClass('light:hover:bg-orange-600');
                            $(handle_trace).addClass('light:active:bg-orange-700');
                            $(handle_trace).addClass('dark:hover:bg-orange-800');
                            $(handle_trace).addClass('dark:active:bg-orange-900');
                        }
                    }
                    else {
                        $(placer).removeClass('bg-white');
                        $(placer).removeClass('border-slate-200');
                        $(placer).removeClass('dark:bg-slate-900');
                        $(placer).addClass('bg-slate-400');
                        $(placer).addClass('border-slate-400');
                        $(placer).addClass('dark:bg-slate-700');
                        $(placer).addClass('dark:border-slate-700');
                        
                        var handle_trace = $(`button[data-tile=${user_sol[i]}]`);
                        $(handle_trace).removeClass('light:bg-slate-200');
                        $(handle_trace).removeClass('light:hover:bg-slate-300');
                        $(handle_trace).removeClass('light:active:bg-slate-400');
                        $(handle_trace).removeClass('dark:bg-slate-600');
                        $(handle_trace).removeClass('dark:hover:bg-slate-700');
                        $(handle_trace).removeClass('dark:active:bg-slate-800');
                        $(handle_trace).removeClass('bg-slate-400');

                        if (!$(handle_trace).hasClass('bg-blue-500') && !$(handle_trace).hasClass('bg-orange-500')) {
                            $(handle_trace).addClass('bg-slate-400');
                        }
                    }
                }
            }
        },
        initHand: function() {
            var handle_input = $('#handle-input');
            $(handle_input).find('button').each(function() {
                if ($(this).attr('data-tile')) {
                    $(this).addClass('light:bg-slate-200');
                    $(this).addClass('light:hover:bg-slate-300');
                    $(this).addClass('light:active:bg-slate-400');
                    $(this).addClass('dark:bg-slate-600');
                    $(this).addClass('dark:hover:bg-slate-700');
                    $(this).addClass('dark:active:bg-slate-800');
                    $(this).removeClass('bg-slate-400');
                    $(this).removeClass('bg-orange-500');
                    $(this).removeClass('light:hover:bg-orange-600');
                    $(this).removeClass('light:active:bg-orange-700');
                    $(this).removeClass('dark:hover:bg-orange-800');
                    $(this).removeClass('dark:active:bg-orange-900');
                    $(this).removeClass('bg-blue-500');
                    $(this).removeClass('hover:bg-blue-600');
                    $(this).removeClass('active:bg-blue-700');
                    $(this).removeClass('text-white');
                }
            });
        },
        toggleLight: function() {
            $('html').removeClass('light');
            $('html').removeClass('dark');

            game.option.light_mode = mode_light + mode_dark - game.option.light_mode;
            game.setLight();
            game.saveGame();
        },
        setLight: function() {
            if (game.option.light_mode == mode_light) $('html').addClass('light');
            else if (game.option.light_mode == mode_dark) $('html').addClass('dark');
        },
        setStage: function() {
            if (!game.option.solution) {
                game.option.round_wind = Math.floor(Math.random() * 276) % 4;
                game.option.round_level = new Date().getDate() % 4 + 1;
                game.option.seat_wind = Math.floor(Math.random() * 1104) % 4;
                game.option.handle_type = Math.floor(Math.random() * 2272) % 2;
                game.option.solution = game.makeSolution();
            }

            $('#handle-stage-info').text(`${game.printWind(game.option.round_wind)}${game.option.round_level}국 | ${game.printWind(game.option.seat_wind)}가 | ${game.printHandle(game.option.handle_type)} 화료`);
        },
        makeSolution: function() {
            var make_type = 2;

            if (make_type == 1) {
                while (true) {
                    var tile_table = [];
                    for (var i = 0; i < game.table.Tiles.length; i++) {
                        tile_table[game.table.Tiles[i]] = 4;
                    }

                    var sol = [];
                    while (sol.length < 14) {
                        var num = Math.floor(Math.random() * 1360) % 34;
                        var tileName = game.table.Tiles[num];
                        tile_table[tileName] -= 1;
                        sol.push(tileName);
                    }

                    if (game.evaluateSolution(sol)) break;
                }
            }
            else if (make_type == 2) {
                while (true) {
                    var tile_table = [];
                    for (var i = 0; i < game.table.Tiles.length; i++) {
                        tile_table[game.table.Tiles[i]] = 4;
                    }

                    var head_cnt = 0;
                    var body_cnt = 0;

                    var sol = [];
                    while (sol.length < 14) {
                        var dice = Math.floor(Math.random() * 1048576) % 1000;
                        if (dice >= 290 && dice <= 485) {
                            if (head_cnt >= 1) continue;

                            while (true) {
                                var num = Math.floor(Math.random() * 1360) % 34;
                                var tileName = game.table.Tiles[num];

                                if (tile_table[tileName] >= 2) {
                                    tile_table[tileName] -= 2;
                                    sol.push(tileName);
                                    sol.push(tileName);
                                    head_cnt += 1;
                                    break;
                                }
                            }
                        }
                        else {
                            if (dice < 290) {
                                while (true) {
                                    var num = Math.floor(Math.random() * 1360) % 34;
                                    var tileName = game.table.Tiles[num];
                                    
                                    if (tile_table[tileName] >= 3) {
                                        tile_table[tileName] -= 3;
                                        sol.push(tileName);
                                        sol.push(tileName);
                                        sol.push(tileName);
                                        body_cnt += 1;
                                        break;
                                    }
                                }
                            }
                            else {
                                var num = 34;
                                while (num >= 27) {
                                    num = Math.floor(Math.random() * 1360) % 34;
                                }

                                while (true) {
                                    var dice = Math.floor(Math.random() * 3333) % 3;
                                    var diceset = [[-2, -1, 0], [-1, 0, 1], [0, 1, 2]];
                                    var numset = [num + diceset[dice][0], num + diceset[dice][1], num + diceset[dice][2]];
                                    var numrange = [Math.floor(numset[0] / 9), Math.floor(numset[1] / 9), Math.floor(numset[2] / 9)];

                                    var wflag = false;

                                    for (var i = 1; i < 3; i++) {
                                        if (numrange[0] != numrange[i]) {
                                            wflag = true;
                                            break;
                                        }
                                    }

                                    if (wflag) continue;
                                    else {
                                        var mflag = true;
                                        for (var i = 0; i < 3; i++) {
                                            var tileName = game.table.Tiles[numset[i]];
                                            if (tile_table[tileName] <= 0) mflag = false;
                                        }

                                        if (mflag) {
                                            for (var i = 0; i < 3; i++) {
                                                var tileName = game.table.Tiles[numset[i]];
                                                tile_table[tileName] -= 1;
                                                sol.push(tileName);
                                                body_cnt += 1;
                                            }

                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    solutionSorted = game.sortSolution(sol);
                    var dice = Math.floor(Math.random() * 91234) % 14;
                    var tmp = solutionSorted[dice];
                    
                    for (var i = dice; i < 13; i++) {
                        solutionSorted[i] = solutionSorted[i + 1];
                    }

                    solutionSorted[13] = tmp;

                    if (game.evaluateSolution(solutionSorted)) {
                        game.option.hand_dice = dice;
                        break;
                    }
                }
            }

            return sol;
        },
        evaluate_backtrack: function(step, tablecopy, depth, candidateSet) {
            if (depth >= 9) {
                game.callback.flag = true;
                game.callback.candidates.head[step - 1] = candidateSet.head;
                game.callback.candidates.body.sets[step - 1] = candidateSet.body.sets;
                game.callback.candidates.body.sequences[step - 1] = candidateSet.body.sequences;

                return true;
            }

            for (var i = 0; i < depth; i++) {
                if (tablecopy[i] != 0) {
                    return false;
                }
            }
            
            if (tablecopy[depth] >= 2) {
                tablecopy[depth] -= 2;
                candidateSet.head[depth] += 1;
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth, game.hardcopy(candidateSet));
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth + 1, game.hardcopy(candidateSet));
                candidateSet.head[depth] -= 1;
                tablecopy[depth] += 2;
            }

            if (tablecopy[depth] >= 3) {
                tablecopy[depth] -= 3;
                candidateSet.body.sets[depth] += 1;
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth, game.hardcopy(candidateSet));
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth + 1, game.hardcopy(candidateSet));
                candidateSet.body.sets[depth] -= 1;
                tablecopy[depth] += 3;
            }
            
            if (tablecopy[depth] >= 1 && tablecopy[depth + 1] >= 1 && tablecopy[depth + 2] >= 1 && depth <= 6) {
                tablecopy[depth] -= 1;
                tablecopy[depth + 1] -= 1;
                tablecopy[depth + 2] -= 1;
                candidateSet.body.sequences[depth] += 1;
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth, game.hardcopy(candidateSet));
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth + 1, game.hardcopy(candidateSet));
                candidateSet.body.sequences[depth] -= 1;
                tablecopy[depth] += 1;
                tablecopy[depth + 1] += 1;
                tablecopy[depth + 2] += 1;
            }

            if (tablecopy[depth] == 0) {
                game.evaluate_backtrack(step, game.hardcopy(tablecopy), depth + 1, game.hardcopy(candidateSet));
            }
        },
        evaluateSolution: function(sol) {
            if (sol.length > 14) return false;

            var tile_table = [];
            for (var i = 0; i < game.table.Tiles.length; i++) {
                tile_table[game.table.Tiles[i]] = 0;
            }

            for (var i = 0; i < sol.length; i++) {
                tile_table[sol[i]] += 1;
            }

            var CNT_HEAD = 0;
            var CNT_BODY = 0;
            var COMPOSITION = false;

            game.initCallback(false);
            
            for (var i = 0; i < 3; i++) {
                var tablecopy = [0, 0, 0, 0, 0, 0, 0, 0, 0];
                for (var j = 0; j < 9; j++) {
                    tablecopy[j] = tile_table[game.table.Tiles[i * 9 + j]];
                }

                game.callback.flag = false;
                var candidateSet = {
                    'head': [0, 0, 0, 0, 0, 0, 0, 0, 0],
                    'body': {
                        'sets': [0, 0, 0, 0, 0, 0, 0, 0, 0],
                        'sequences': [0, 0, 0, 0, 0, 0, 0, 0, 0],
                    },
                };

                game.evaluate_backtrack(i + 1, tablecopy, 0, candidateSet);
            }

            for (var i = 27; i < 34; i++) {
                if (tile_table[game.table.Tiles[i]] == 3) {
                    game.callback.candidates.body.sets[Math.floor(i / 9)][i % 9] += 1;
                }
                if (tile_table[game.table.Tiles[i]] == 2) CNT_HEAD += 1;
            }
            
            for (var i = 0; i < 34; i++) {
                CNT_HEAD += game.callback.candidates.head[Math.floor(i / 9)][i % 9];
                CNT_BODY += game.callback.candidates.body.sets[Math.floor(i / 9)][i % 9];
                CNT_BODY += game.callback.candidates.body.sequences[Math.floor(i / 9)][i % 9];
            }

            var YAKU_ALL_SIMPLES = true; // v
            var YAKU_CHANTA = true; // v
            var YAKU_7HEAD = true; // v
            var YAKU_IDENTICAL_SEQUENCES = false; // v
            var YAKU_ALL_IDENTICAL = false; // v
            var YAKU_MIXED_IDENTICAL = false; // v
            var YAKU_TRIPLE_IDENTICAL = false; // v
            var YAKU_TRIPLE_SEQUENCES = false; // v
            var YAKU_FULL_SEQUENCES = false; // v
            var YAKU_ALL_SETS = false; // v
            var YAKU_NO_POINTS_HAND = false; // v
            var YAKU_TRIPLET = false;
            var YAKU_Z = false; // v
            var YAKU = false;

            var YAKUMAN_GUKSAMUSSANG = true; // v
            var YAKUMAN_ALL_TERMINALS = false; // v
            var YAKUMAN = false;

            var guksa_table = [0, 8, 9, 17, 18, 26, 27, 28, 29, 30, 31, 32, 33];
            for (var i = 0; i < guksa_table.length; i++) {
                if (tile_table[game.table.Tiles[guksa_table[i]]] == 0) YAKUMAN_GUKSAMUSSANG = false;
            }

            for (var i = 0; i < game.table.Tiles.length; i++) {
                if (tile_table[game.table.Tiles[i]] != 2 && tile_table[game.table.Tiles[i]] != 0) YAKU_7HEAD = false;
            }

            if ((CNT_HEAD == 1 && CNT_BODY == 4) ||
                CNT_HEAD == 7 || YAKU_7HEAD || YAKUMAN_GUKSAMUSSANG) COMPOSITION = true;

            if (COMPOSITION && game.option.handle_type == handle_tsumo) return true;

            var COND_MIXED = false;
            var COND_ALL_TERMINALS = false;
            var COND_ALL_IDENTICAL = false;

            var CNT_SETS = 0;
            var CNT_SEQUENCES = 0;

            for (var i = 0; i < 9; i++) {
                if (game.callback.candidates.body.sets[0][i] >= 1 && game.isEqual3(game.callback.candidates.body.sets[0][i], game.callback.candidates.body.sets[1][i], game.callback.candidates.body.sets[2][i])) {
                    YAKU_TRIPLE_IDENTICAL = true;
                }
                if (game.callback.candidates.body.sequences[0][i] >= 1 && game.isEqual3(game.callback.candidates.body.sequences[0][i], game.callback.candidates.body.sequences[1][i], game.callback.candidates.body.sequences[2][i])) {
                    YAKU_TRIPLE_SEQUENCES = true;
                }
                for (var j = 0; j < 3; j++) {
                    CNT_SETS += game.callback.candidates.body.sets[j][i];
                    CNT_SEQUENCES += game.callback.candidates.body.sequences[j][i];

                    if (game.callback.candidates.body.sequences[j][i] >= 2) {
                        YAKU_IDENTICAL_SEQUENCES = true;
                    }
                }
            }

            if (CNT_SETS >= 3) YAKU_TRIPLET = true;
            if (CNT_SETS >= 4) YAKU_ALL_SETS = true;
            if (CNT_SEQUENCES >= 4) {
                var hand = sol[13];
                var hand_idx = game.table.Tiles.indexOf(hand);

                if (hand_idx < 27) {
                    var hand_step = Math.floor(hand_idx / 9);
                    var hand_depth = hand_idx % 9;

                    if (hand_depth >= 1 && hand_depth < 6 && game.callback.candidates.body.sequences[hand_step][hand_depth] >= 1) YAKU_NO_POINTS_HAND = true;
                    if (hand_depth >= 3 && game.callback.candidates.body.sequences[hand_step][hand_depth - 2] >= 1) YAKU_NO_POINTS_HAND = true;
                }
            }

            for (var i = 0; i < 3; i++) {
                var COND_FULL_SEQUENCES = true;
                for (var j = 0; j < 9; j++) {
                    if (tile_table[game.table.Tiles[9 * i + j]] == 0) COND_FULL_SEQUENCES = false;
                }

                if (COND_FULL_SEQUENCES) YAKU_FULL_SEQUENCES = true;
            }

            for (var i = 0; i < 27; i++) {
                if (tile_table[game.table.Tiles[i]] >= 1) {
                    if (i % 9 == 0 || i % 9 == 8) YAKU_ALL_SIMPLES = false;
                    if (i % 9 != 0 && i % 9 != 8) COND_ALL_TERMINALS = false;
                    if (i % 9 >= 3 && i % 9 <= 5) YAKU_CHANTA = false;
                }
            }

            for (var i = 27; i < 34; i++) {
                if (tile_table[game.table.Tiles[i]] >= 3) {
                    if (i == 27 + game.option.round_wind) YAKU_Z = true;
                    else if (i == 27 + game.option.seat_wind) YAKU_Z = true;
                    else if (i >= 31) YAKU_Z = true;
                }
            }

            if (!COND_MIXED && COND_ALL_TERMINALS) YAKUMAN_ALL_TERMINALS = true;

            var mixed_table = [0, 0, 0, 0];
            for (var i = 0; i < 34; i++) {
                if (tile_table[game.table.Tiles[i]] >= 1) {
                    mixed_table[Math.floor(i / 9)] += 1;
                }
            }

            
            if ((mixed_table[0] >= 1 && mixed_table[1] == 0 && mixed_table[2] == 0) ||
                (mixed_table[0] == 0 && mixed_table[1] >= 1 && mixed_table[2] == 0) ||
                (mixed_table[0] == 0 && mixed_table[1] == 0 && mixed_table[2] >= 1)) {
                COND_ALL_IDENTICAL = true;
            }

            if (mixed_table[3] >= 1) COND_MIXED = true;

            var mixed_tmp = -1;
            for (var i = 0; i < 3; i++) {
                if (mixed_table[i] >= 0) {
                    if (mixed_tmp == -1) { mixed_tmp = i; }
                    else { COND_ALL_IDENTICAL = false; }
                }
            }

            if (COND_MIXED && COND_ALL_IDENTICAL) YAKU_MIXED_IDENTICAL = true;
            if (!COND_MIXED && COND_ALL_IDENTICAL) YAKU_ALL_IDENTICAL = true;

            if (YAKU_ALL_SIMPLES ||
                YAKU_CHANTA ||
                YAKU_7HEAD ||
                YAKU_IDENTICAL_SEQUENCES ||
                YAKU_ALL_IDENTICAL ||
                YAKU_MIXED_IDENTICAL ||
                YAKU_TRIPLE_IDENTICAL ||
                YAKU_TRIPLE_SEQUENCES ||
                YAKU_FULL_SEQUENCES ||
                YAKU_ALL_SETS ||
                YAKU_NO_POINTS_HAND ||
                YAKU_TRIPLET ||
                YAKU_Z) YAKU = true;

            if (YAKUMAN_GUKSAMUSSANG ||
                YAKUMAN_ALL_TERMINALS) YAKUMAN = true;

            if (game.option.debug) {
                console.log(`YAKU_ALL_SIMPLES `, YAKU_ALL_SIMPLES);
                console.log(`YAKU_CHANTA `, YAKU_CHANTA);
                console.log(`YAKU_7HEAD `, YAKU_7HEAD);
                console.log(`YAKU_IDENTICAL_SEQUENCES `, YAKU_IDENTICAL_SEQUENCES);
                console.log(`YAKU_ALL_IDENTICAL `, YAKU_ALL_IDENTICAL);
                console.log(`YAKU_MIXED_IDENTICAL `, YAKU_MIXED_IDENTICAL);
                console.log(`YAKU_TRIPLE_IDENTICAL `, YAKU_TRIPLE_IDENTICAL);
                console.log(`YAKU_TRIPLE_SEQUENCES `, YAKU_TRIPLE_SEQUENCES);
                console.log(`YAKU_FULL_SEQUENCES `, YAKU_FULL_SEQUENCES);
                console.log(`YAKU_ALL_SETS `, YAKU_ALL_SETS);
                console.log(`YAKU_NO_POINTS_HAND `, YAKU_NO_POINTS_HAND);
                console.log(`YAKU_TRIPLET `, YAKU_TRIPLET);
                console.log(`YAKU_Z `, YAKU_Z);
                console.log(`YAKU `, YAKU);
                console.log(`YAKUMAN_GUKSAMUSSANG `, YAKUMAN_GUKSAMUSSANG);
                console.log(`YAKUMAN_ALL_TERMINALS `, YAKUMAN_ALL_TERMINALS);
                console.log(`YAKUMAN `, YAKUMAN);
            }

            if (!COMPOSITION && !YAKU_7HEAD && !YAKUMAN) return false;
            if (!YAKU && !YAKUMAN) return false;

            /*
            console.log(`body ${CNT_BODY} head ${CNT_HEAD}`);
            console.log('cond_mixed ', COND_MIXED);
            console.log('cond_all_terminals ', COND_ALL_TERMINALS);
            console.log('cond_all_identical ', COND_ALL_IDENTICAL);
            console.log('yaku_triple_identical', YAKU_TRIPLE_IDENTICAL);
            console.log('yaku_triple_sequences', YAKU_TRIPLE_SEQUENCES);
            console.log('composition ', COMPOSITION);
            console.log('yaku ', YAKU);
            console.log('yakuman ', YAKUMAN);
            */

            return true;
        },
        sortSolution: function(sol, sortLast = true) {
            var lastIdx = sol.length;
            if (!sortLast) lastIdx -= 1;

            for (var i = 0; i < lastIdx; i++) {
                for (var j = i + 1; j < lastIdx; j++) {
                    var i_idx = game.table.Tiles.indexOf(sol[i]);
                    var j_idx = game.table.Tiles.indexOf(sol[j]);

                    if (i_idx > j_idx) {
                        var tmp = sol[i];
                        sol[i] = sol[j];
                        sol[j] = tmp;
                    }
                }
            }

            return sol;
        },
        table: {
            Tiles: ["Man1", "Man2", "Man3", "Man4", "Man5", "Man6", "Man7", "Man8", "Man9",
                    "Pin1", "Pin2", "Pin3", "Pin4", "Pin5", "Pin6", "Pin7", "Pin8", "Pin9",
                    "Sou1", "Sou2", "Sou3", "Sou4", "Sou5", "Sou6", "Sou7", "Sou8", "Sou9",
                    "Ton", "Nan", "Shaa", "Pei", "Haku", "Hatsu", "Chun"],
            Ids: ["1m", "2m", "3m", "4m", "5m", "6m", "7m", "8m", "9m",
                "1p", "2p", "3p", "4p", "5p", "6p", "7p", "8p", "9p",
                "1s", "2s", "3s", "4s", "5s", "6s", "7s", "8s", "9s",
                "1z", "2z", "3z", "4z", "5z", "6z", "7z"],
            encode_code: 'TbpSQWwCc46XOBd9aJiN1rMFLjoIPVvtgumezkfHUAlYyx2Z8Dhsn305RGEqK7'
        },
        inputTile: function(tile) {
            if (game.option.guessed_depth >= game.option.guess_depth || game.option.tiles_placed >= 14 || game.option.end) return;

            var handle = $(`#handle-depth-${game.option.guessed_depth + 1}`);
            var placer = $(handle).find(`div.handle-item-${game.option.tiles_placed + 1}`);

            $(placer).addClass('border-black');
            $(placer).addClass('dark:border-slate-100');
            $(placer).addClass('cell-animation');

            var placer_script = `<img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/${tile}.svg" alt="${tile}">`;
            placer_script += `<img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/${tile}.svg" alt="${tile}">`;
            $(placer).html(placer_script);

            game.option.tiles_placed += 1;
        },
        removeTile: function() {
            if (game.option.guessed_depth >= game.option.guess_depth || game.option.tiles_placed < 1 || game.option.end) return;

            var handle = $(`#handle-depth-${game.option.guessed_depth + 1}`);
            var placer = $(handle).find(`div.handle-item-${game.option.tiles_placed}`);

            $(placer).removeClass('border-black');
            $(placer).removeClass('dark:border-slate-100');
            $(placer).removeClass('cell-animation');
            $(placer).html('');

            game.option.tiles_placed -= 1;
        },
        showInvalidEnter: function() {
            $('#handle-guess').animate({
                width: '104%',
                opacity: 0.4
            }, 100, function() {
                $('#handle-guess').animate({
                    width: '100%',
                    opacity: 1
                }, 100);
            });
        },
        enterTile: function() {
            if (game.option.tiles_placed != 14 || game.option.end) {
                game.showInvalidEnter();
                return;
            }

            var handle = $(`#handle-depth-${game.option.guessed_depth + 1}`);
            var user_sol = [];
            for (var i = 0; i < 14; i++) {
                var placer = $(handle).find(`div.handle-item-${i + 1}`);
                var tileName = $(placer).find(`img`).attr('src').split('.svg')[0].split('light/')[1];
                user_sol.push(tileName);
            }

            if (!game.evaluateSolution(user_sol)) {
                game.showInvalidEnter();
                return;
            }

            game.highlightGuess(game.option.guessed_depth + 1);

            var solve_cnt = 0;
            for (var i = 0; i < 14; i++) {
                if (user_sol[i] == game.option.solution[i]) solve_cnt += 1;
            }

            if (solve_cnt >= 14) {
                game.statistics[game.option.guessed_depth] += 1;
                game.streaks.current_streak += 1;
                game.streaks.best_streak = Math.max(game.streaks.best_streak, game.streaks.current_streak);
                game.option.tiles_placed = 0;
                game.guesses.push(user_sol);
                game.option.end = true;
                game.showStatistics();
                game.saveGame();

                if (game.callback.load_from_code) {
                    location.replace(game.url);
                }
            }
            else {
                game.option.guessed_depth += 1;
                game.option.tiles_placed = 0;
                game.guesses.push(user_sol);

                if (game.option.guessed_depth >= game.option.guess_depth) {
                    game.statistics[game.option.guess_depth] += 1;
                    game.streaks.current_streak = 0;
                    game.option.end = true;
                    game.showAnswer();
                    game.showStatistics();

                    if (game.callback.load_from_code) {
                        location.replace(game.url);
                    }
                }

                game.saveGame();
            }
        },
        showInfo: function() {
            game.callback.dialog_timer = true;
            setTimeout(function() {
                game.callback.dialog_timer = false;
            }, 333);

            $('#headlessui-portal-root').removeClass('displaynone');
            $('#headlessui-dialog-info').removeClass('displaynone');
            $('#headlessui-dialog-statistics').addClass('displaynone');
            $('#headlessui-portal-root').stop().animate({
                opacity: 1
            }, 500);
        },
        hideInfo: function() {
            $('#headlessui-portal-root').stop().animate({
                opacity: 0
            }, 500, function() {
                $('#headlessui-portal-root').addClass('displaynone');
                $('#headlessui-dialog-info').addClass('displaynone');
            });
        },
        showStatistics: function() {
            var d_statistics = $('#headlessui-dialog-statistics');
            var total_games = 0;
            var statistics_max = 0;
            for (var i = 0; i < game.option.guess_depth + 1; i++) {
                if (i <= game.option.guess_depth - 1) statistics_max = Math.max(statistics_max, game.statistics[i]);
                total_games += game.statistics[i];
            }

            for (var i = 0; i < game.option.guess_depth; i++) {
                var tryTag = $(d_statistics).find(`.statistics-try-${i + 1}`);
                $(tryTag).text(game.statistics[i]);

                var tryTagWidth = 5;
                if (game.statistics[i] > 0) tryTagWidth = Math.round(game.statistics[i] / statistics_max * 90) + 5;
                $(tryTag).css('width', `${tryTagWidth}%`);
            }

            var win_rate = 0;
            if (total_games >= 1 && game.statistics[game.option.guess_depth] > 0) {
                win_rate = Math.ceil((total_games - game.statistics[game.option.guess_depth]) / total_games * 100);
            }
            else if (total_games >= 1) {
                win_rate = 100;
            }

            $(d_statistics).find('.statistics-total-games').text(total_games);
            $(d_statistics).find('.statistics-win-rate').text(`${win_rate}%`);
            $(d_statistics).find('.statistics-current-streak').text(`${game.streaks.current_streak}`);
            $(d_statistics).find('.statistics-best-streak').text(`${game.streaks.best_streak}`);

            game.callback.dialog_timer = true;
            setTimeout(function() {
                game.callback.dialog_timer = false;
            }, 333);

            $('#headlessui-portal-root').removeClass('displaynone');
            $('#headlessui-dialog-info').addClass('displaynone');
            $('#headlessui-dialog-statistics').removeClass('displaynone');
            $('#headlessui-portal-root').stop().animate({
                opacity: 1
            }, 500);
        },
        hideStatistics: function() {
            $('#headlessui-portal-root').stop().animate({
                opacity: 0
            }, 500, function() {
                $('#headlessui-portal-root').addClass('displaynone');
                $('#headlessui-dialog-statistics').addClass('displaynone');
            });
        },
        showAnswer: function() {
            var d_statistics = $('#headlessui-dialog-statistics');
            var answerWrapper = $(d_statistics).find('#statistics-answer-wrapper');
            $(answerWrapper).removeClass('displaynone');
            var answerLine = $(answerWrapper).find('#statistics-answer');
            $(answerLine).html('');

            for (var i = 0; i < 14; i++) {
                var tileTag = `<div class="handle-item last:md:ml-2 last:ml-1 md:w-12 sm:w-9 w-6 md:h-16 sm:h-12 h-8 border-solid md:border-2 border flex items-center justify-center sm:mx-0.5 md:text-4xl sm:text-2xl text-xl rounded dark:text-white last:border-lime-300">
                                    <img class="p-1 light:block dark:hidden drop-shadow-tile-light" src="/tiles/light/${game.option.solution[i]}.svg" alt="${game.option.solution[i]}">
                                    <img class="p-1 light:hidden dark:block drop-shadow-tile-dark" src="/tiles/dark/${game.option.solution[i]}.svg" alt="${game.option.solution[i]}">
                                </div>`;
                $(answerLine).append(tileTag);
            }
        },
        hideAnswer: function() {
            var d_statistics = $('#headlessui-dialog-statistics');
            var answerWrapper = $(d_statistics).find('#statistics-answer-wrapper');
            $(answerWrapper).addClass('displaynone');
            var answerLine = $(answerWrapper).find('#statistics-answer');
            $(answerLine).html('');
        },
        printWind: function(w) {
            return ["동", "남", "서", "북"][w];
        },
        printHandle: function(w) {
            return ["쯔모", "론"][w];
        },
        hardcopy: function(val) {
            return JSON.parse(JSON.stringify(val));
        },
        isEqual3: function(val1, val2, val3) {
            return (val1 === val2) && (val2 === val3);
        },
        makeCodeTable: function() {
            var code_table = [];

            while (code_table.length < 62) {
                var dice = Math.floor(Math.random() * 91234) % 62;
                var code_str = '';
                if (dice <= 25) code_str = String.fromCharCode(97 + dice);
                else if (dice >= 26 && dice <= 51) code_str = String.fromCharCode(65 + dice - 26);
                else code_str = String.fromCharCode(48 + dice - 52);

                if (code_table.indexOf(code_str) == -1) {
                    code_table.push(code_str);
                }
            }

            return code_table;
        }
    };

    $(document).click(function(e) {
        if ($('#headlessui-portal-root').hasClass('displaynone')) return true;
        if (game.callback.dialog_timer) return true;
        
        var d_info = $('#headlessui-dialog-info');
        var d_statistics = $('#headlessui-dialog-statistics');

        if (!$(d_info).hasClass('displaynone')) {
            if ($('#headlessui-info').has(e.target).length == 0) {
                game.hideInfo();
            }
        }

        if (!$(d_statistics).hasClass('displaynone')) {
            if ($('#headlessui-statistics').has(e.target).length == 0) {
                game.hideStatistics();
            }
        }
    });

    $('#svg-info').click(function() {
        game.showInfo();
    });

    $('#btn-info').click(function() {
        game.showInfo();
    });

    $('#svg-toggle-light').click(function() {
        game.toggleLight();
    });

    $('#svg-chart').click(function() {
        game.showStatistics();
    });

    $('#svg-close-info').click(function() {
        game.hideInfo();
    });

    $('#svg-close-statistics').click(function() {
        game.hideStatistics();
    });

    $('#statistics-next-game').click(function() {
        game.hideStatistics();
        game.newGame();
    });

    $('#statistics-share-game').click(function() {
        game.shareGame();
    });

    $('#handle-input').find('button.flex.items-center').click(function() { 
        var tile = $(this).attr('data-tile');
        if (tile == 'btn-enter') {
            game.enterTile();
        }
        else if (tile == 'btn-delete') {
            game.removeTile();
        }
        else {
            game.inputTile(tile);
        }
    });

    $(document).keypress(function(e) {
        game.presses += String.fromCharCode(e.keyCode).toLowerCase();

        for (var i = 0; i < game.table.Ids.length; i++) {
            if (game.presses.endsWith(game.table.Ids[i])) {
                game.inputTile(game.table.Tiles[i]);
                game.presses = '';
                break;
            }
        }
    });

    $(document).keydown(function(e) {
        if (e.keyCode == 8) game.removeTile();
        if (e.keyCode == 13) game.enterTile();
    });
    
    game.init();
});