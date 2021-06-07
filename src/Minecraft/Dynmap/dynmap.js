'use strict';

let dynmapversion = "1.9.1-1832-Dev";

function createMinecraftHead(player,size,completed,failed) {
    let faceImage = new Image();
    faceImage.onload = function() {
        completed(faceImage);
    };
    faceImage.onerror = function() {
        failed();
    };
    let faceimg;
    if(size == 'body')
        faceimg = 'faces/body/' + player + '.png';
    else
        faceimg = 'faces/' + size + 'x' + size + '/' + player + '.png';
    let url = dynmap.options.url.markers;
    if(url.indexOf('?') >= 0)
        faceImage.src = url + escape(faceimg);
    else
        faceImage.src = url + faceimg;

}

function getMinecraftHead(player,size,completed) {
    createMinecraftHead(player, size, completed, function() {
        console.error('Failed to retrieve face of "', player, '" with size "', size, '"!')
    });
}

function getMinecraftTime(servertime) {
    servertime = parseInt(servertime);
    let day = servertime >= 0 && servertime < 13700;
    return {
        servertime: servertime,
        days: parseInt((servertime+8000) / 24000),

        // Assuming it is day at 6:00
        hours: (parseInt(servertime / 1000)+6) % 24,
        minutes: parseInt(((servertime / 1000) % 1) * 60),
        seconds: parseInt(((((servertime / 1000) % 1) * 60) % 1) * 60),

        day: day,
        night: !day
    };
}

function chat_encoder(message) {
    if (dynmap.options.cyrillic) {
        if(message.source === 'player') {
            let utftext = "";
            for (let n = 0; n < message.text.length; n++) {
                let c = message.text.charCodeAt(n);
                if (c >= 192) {
                    c = message.text.charCodeAt(n);
                    utftext += String.fromCharCode(c+848);
                }
                else if (c == 184) { utftext += String.fromCharCode(1105); }
                else {
                    utftext += String.fromCharCode(c);
                }
            }
            return utftext
        }
    }
    return message.text;
}

let componentconstructors = {};
let maptypes = {};
let map = null;	// Leaflet assumes top-level 'map'...

componentconstructors['testcomponent'] = function(dynmap, configuration) {
    console.log('initialize');
    $(dynmap).bind('worldchanged', function() { console.log('worldchanged'); });
    $(dynmap).bind('mapchanging', function() { console.log('mapchanging'); });
    $(dynmap).bind('mapchanged', function() { console.log('mapchanged'); });
    $(dynmap).bind('zoomchanged', function() { console.log('zoomchanged'); });
    $(dynmap).bind('worldupdating', function() { console.log('worldupdating'); });
    $(dynmap).bind('worldupdate', function() { console.log('worldupdate'); });
    $(dynmap).bind('worldupdated', function() { console.log('worldupdated'); });
    $(dynmap).bind('worldupdatefailed', function() { console.log('worldupdatefailed'); });
    $(dynmap).bind('playeradded', function() { console.log('playeradded'); });
    $(dynmap).bind('playerremoved', function() { console.log('playerremoved'); });
    $(dynmap).bind('playerupdated', function() { console.log('playerupdated'); });
};

function DynMap(options) {
    let me = this;
    if(me.checkForSavedURL())
        return;
    me.options = options;
    $.getJSON(me.options.url.configuration, function(configuration) {
        if(configuration.error == 'login-required') {
            me.saveURL();
            window.location = 'login.html';
        }
        else if(configuration.error) {
            alert(configuration.error);
        }
        else {
            me.configure(configuration);
            me.initialize();
        }
    }, function(status, statusMessage) {
        alert('Could not retrieve configuration: ' + statusMessage);
    });
}
DynMap.prototype = {
    components: [],
    worlds: {},
    registeredTiles: [],
    players: {},
    lasttimestamp: new Date().getUTCMilliseconds(), /* Pseudorandom - prevent cached '?0' */
    reqid: 0,
    servertime: 0,
    serverday: false,
    inittime: new Date().getTime(),
    followingPlayer: '',
    initfollow: null,
    missedupdates: 0,
    maxcount: -1,
    currentcount: 0,
    playerfield: null,
    layercontrol: undefined,
    nogui: false,
    formatUrl: function(name, options) {
        let url = this.options.url[name];
        $.each(options, function(n,v) {
            url = url.replace("{" + n + "}", v);
        });
        return url;
    },
    configure: function(configuration) {
        let me = this;
        $.extend(me.options, configuration);

        $.each(me.options.worlds, function(index, worldentry) {
            let world = me.worlds[worldentry.name] = $.extend({}, worldentry, {
                maps: {}
            });

            $.each(worldentry.maps, function(index, mapentry) {
                let map = $.extend({}, mapentry, {
                    world: world,
                    dynmap: me
                });
                map = world.maps[mapentry.name] = maptypes[mapentry.type](map);
                if(me.options.defaultmap && me.options.defaultmap == mapentry.name)
                    world.defaultmap = map;
                world.defaultmap = world.defaultmap || map;
            });
            me.defaultworld = me.defaultworld || world;
        });
        let urlarg = me.getParameterByName('worldname');
        if(urlarg == "")
            urlarg = me.options.defaultworld || "";
        if(urlarg != "") {
            me.defaultworld = me.worlds[urlarg] || me.defaultworld;
        }
        urlarg = me.getParameterByName('mapname');
        if(urlarg != "") {
            me.defaultworld.defaultmap = me.defaultworld.maps[urlarg] || me.defaultworld.defaultmap;
        }
        urlarg = me.getIntParameterByName('x');
        if(urlarg != null)
            me.defaultworld.center.x = urlarg;
        urlarg = me.getIntParameterByName('y');
        if(urlarg != null)
            me.defaultworld.center.y = urlarg;
        urlarg = me.getIntParameterByName('z');
        if(urlarg != null)
            me.defaultworld.center.z = urlarg;
        urlarg = me.getParameterByName('nogui');
        if(urlarg != "") {
            me.nogui = (urlarg == 'true');
        }
    },
    initialize: function() {
        let me = this;

        // Get a handle to the DOM element which acts as the overall container and apply a class of
        // "dynmap" to it.
        let container = $(me.options.container);
        container.addClass('dynmap');

        // Create a new container within the main container which actually holds the map. It needs a
        // class of "map".
        let mapContainer;
        (mapContainer = $('<div/>'))
            .addClass('map')
            .appendTo(container);

        // Set the title if the options specify one.
        if(me.options.title)
            document.title = me.options.title;

        // Try to set the default zoom level based on the URL parameter.
        let urlzoom = me.getIntParameterByName('zoom');
        if(urlzoom != null)
            me.options.defaultzoom = urlzoom;

        // Decide whether or not the layer control will be visible based on the URL parameter or
        // or fallback to the options
        let showlayerctl = me.getParameterByName('showlayercontrol');
        if(showlayerctl != "")
            me.options.showlayercontrol = showlayerctl;

        // If we still don't have a default zoom level, force it to be 1
        if(typeof me.options.defaultzoom == 'undefined')
            me.options.defaultzoom = 1;

        // Decide whether we should be following a given player or not based solely on URL parameter.
        let initfollowplayer = me.getParameterByName('playername');
        if(initfollowplayer != "")
            me.initfollow = initfollowplayer;

        // Derive the state of the sidebar based on the URL parameter.
        let sidebaropen = me.getParameterByName('sidebaropened');
        if(sidebaropen == 'false' || sidebaropen == 'true' || sidebaropen == 'pinned')
            me.options.sidebaropened = sidebaropen;

        let map = this.map = new L.Map(mapContainer.get(0), {
            zoom: me.options.defaultzoom,
            center: new L.LatLng(0, 0),
            zoomAnimation: true,
            zoomControl: !me.nogui,
            attributionControl: false,
            crs: L.extend({}, L.CRS, {
                code: 'simple',
                projection: {
                    project: function(latlng) {
                        // Direct translation of lat -> x, lng -> y.
                        return new L.Point(latlng.lat, latlng.lng);
                    },
                    unproject: function(point) {
                        // Direct translation of x -> lat, y -> lng.
                        return new L.LatLng(point.x, point.y);
                    }
                },
                // a = 1; b = 2; c = 1; d = 0
                // x = a * x + b; y = c * y + d
                // End result is 1:1 values during transformation.
                transformation: new L.Transformation(1, 0, 1, 0),
                scale: function(zoom) {
                    // Equivalent to 2 raised to the power of zoom, but faster.
                    return (1 << zoom);
                }
            }),
            continuousWorld: true,
            worldCopyJump: false
        });
        window.map = map; // Placate Leaflet need for top-level 'map'....

        map.on('zoomend', function() {
            me.maptype.updateTileSize(me.map.getZoom());
            $(me).trigger('zoomchanged');
        });

        /*google.maps.event.addListener(map, 'dragstart', function(mEvent) {
            me.followPlayer(null);
        });*/

        // Sidebar
        let panel;
        let sidebar;
        let pinbutton;
        let nopanel = (me.getParameterByName('nopanel') == 'true') || me.nogui;

        if(me.options.sidebaropened != 'true') { // false or pinned
            let pincls = 'pinned';
            if(me.options.sidebaropened == 'false')
                pincls = '';

            sidebar = me.sidebar = $('<div/>')
                .addClass('sidebar ' + pincls);

            panel = $('<div/>')
                .addClass('panel')
                .appendTo(sidebar);

            // Pin button.
            pinbutton = $('<div/>')
                .addClass('pin')
                .click(function() {
                    sidebar.toggleClass('pinned');
                })
                .appendTo(panel);
        }
        else {
            sidebar = me.sidebar = $('<div/>')
                .addClass('sidebar pinned');

            panel = $('<div/>')
                .addClass('panel')
                .appendTo(sidebar);
        }
        if(!nopanel)
            sidebar.appendTo(container);

        // World scrollbuttons
        let upbtn_world = $('<div/>')
            .addClass('scrollup')
            .bind('mousedown mouseup touchstart touchend', function(event){
                if(event.type == 'mousedown' || event.type == 'touchstart'){
                    worldlist.animate({"scrollTop": "-=300px"}, 3000, 'linear');
                }else{
                    worldlist.stop();
                }
            });
        let downbtn_world = $('<div/>')
            .addClass('scrolldown')
            .bind('mousedown mouseup touchstart touchend', function(event){
                if(event.type == 'mousedown' || event.type == 'touchstart'){
                    worldlist.animate({"scrollTop": "+=300px"}, 3000, 'linear');
                }else{
                    worldlist.stop();
                }
            });

        // Worlds
        let worldlist;
        $('<fieldset/>')
            .append($('<legend/>').text(me.options['msg-maptypes']))
            .append(upbtn_world)
            .append(me.worldlist = worldlist = $('<ul/>').addClass('worldlist')
                .bind('mousewheel', function(event, delta){
                    this.scrollTop -= (delta * 10);
                    event.preventDefault();
                })
            )
            .append(downbtn_world)
            .appendTo(panel);

        let maplists = {};
        let worldsadded = {};
        $.each(me.worlds, function(index, world) {
            let maplist;
            world.element = $('<li/>')
                .addClass('world')
                .text(world.title)
                .append(maplist = $('<ul/>')
                    .addClass('maplist')
                )
                .data('world', world);
            maplists[world.name] = maplist;
        });

        $.each(me.worlds, function(index, world) {
            let maplist = maplists[world.name];

            $.each(world.maps, function(mapindex, map) {
                //me.map.mapTypes.set(map.world.name + '.' + map.name, map);
                let wname = world.name;
                if(map.options.append_to_world) {
                    wname = map.options.append_to_world;
                }
                let mlist = maplists[wname];
                if(!mlist) {
                    mlist = maplist;
                    wname = world.name;
                }
                if(!worldsadded[wname]) {
                    worldsadded[wname] = true;
                }

                map.element = $('<li/>')
                    .addClass('map')
                    .append($('<a/>')
                        .attr({ title: map.options.title, href: '#' })
                        .addClass('maptype')
                        .css({ backgroundImage: 'url(' + (map.options.icon || ('images/block_' + mapindex + '.png')) + ')' })
                        .text(map.options.title)
                    )
                    .click(function() {
                        me.selectMap(map);
                    })
                    .data('map', map)
                    .appendTo(mlist);
            });
        });
        $.each(me.worlds, function(index, world) {
            if(worldsadded[world.name]) {
                world.element.appendTo(worldlist);
            }
        });

        // The scrollbuttons
        // we need to show/hide them depending: if (me.playerlist.scrollHeight() > me.playerlist.innerHeight()) or something.
        let upbtn = $('<div/>')
            .addClass('scrollup')
            .bind('mousedown mouseup touchstart touchend', function(event){
                if(event.type == 'mousedown' || event.type == 'touchstart'){
                    playerlist.animate({"scrollTop": "-=300px"}, 3000, 'linear');
                }else{
                    playerlist.stop();
                }
            });
        let downbtn = $('<div/>')
            .addClass('scrolldown')
            .bind('mousedown mouseup touchstart touchend', function(event){
                if(event.type == 'mousedown' || event.type == 'touchstart'){
                    playerlist.animate({"scrollTop": "+=300px"}, 3000, 'linear');
                }else{
                    playerlist.stop();
                }
            });

        // The Player List
        let playerlist;
        $('<fieldset/>')
            .append(me.playerfield = $('<legend/>').text(me.options['msg-players']))
            .append(upbtn)
            .append(me.playerlist = playerlist = $('<ul/>').addClass('playerlist')
                .bind('mousewheel', function(event, delta){
                    this.scrollTop -= (delta * 10);
                    event.preventDefault();
                })
            )
            .append(downbtn)
            .appendTo(panel);

        let updateHeight = function() {
            if(sidebar.innerHeight() > (2*worldlist.scrollHeight())) { /* Big enough */
                worldlist.height(worldlist.scrollHeight());
                upbtn_world.toggle(false);
                downbtn_world.toggle(false);
            }
            else{
                worldlist.height(sidebar.innerHeight() / 2);
                upbtn_world.toggle(true);
                downbtn_world.toggle(true);
            }
            playerlist.height(sidebar.innerHeight() - (playerlist.offset().top - worldlist.offset().top) - 64); // here we need a fix to avoid the static value, but it works fine this way :P
            let scrollable = playerlist.scrollHeight() > playerlist.height();
            upbtn.toggle(scrollable);
            downbtn.toggle(scrollable);
        };
        updateHeight();
        $(window).resize(updateHeight);
        $(dynmap).bind('playeradded', function() {
            updateHeight();
        });
        $(dynmap).bind('playerremoved', function() {
            updateHeight();
        });
        // The Compass
        let compass = $('<div/>').
        addClass('compass');
        if(L.Browser.mobile)
            compass.addClass('mobilecompass');
        if (!me.nogui) {
            compass.appendTo(container);
        }

        if(me.options.sidebaropened != 'true') {
            $('<div/>')
                .addClass('hitbar')
                .click(function() {
                    sidebar.toggleClass('pinned');
                })
                .appendTo(panel);
        }

        me.alertbox = $('<div/>')
            .addClass('alertbox')
            .hide()
            .appendTo(container);

        if((dynmapversion != me.options.coreversion) && (dynmapversion.indexOf("-Dev") < 0)) { // Disable on dev builds
            me.alertbox
                .text('Web files are not matched with plugin version: All files need to be same version (' + me.options.dynmapversion + ') - try refreshing browser cache (shift-reload)')
                .show();
            return;
        }

        me.initLogin();

        me.selectMap(me.defaultworld.defaultmap);

        let componentstoload = 0;
        let configset = { };
        if (!me.nogui) {
            $.each(me.options.components, function(index, configuration) {
                if(!configset[configuration.type]) {
                    configset[configuration.type] = [];
                    componentstoload++;
                }
                configset[configuration.type].push(configuration);
            });
        }

        let tobeloaded = {};
        $.each(configset, function(type, configlist) {
            tobeloaded[type] = true;
            loadjs('js/' + type + '.js', function() {
                let componentconstructor = componentconstructors[type];
                if (componentconstructor) {
                    $.each(configlist, function(idx, configuration) {
                        me.components.push(new componentconstructor(me, configuration));
                    });
                } else {
                    // Could not load component. We'll ignore this for the moment.
                }
                delete tobeloaded[type];
                componentstoload--;
                if (componentstoload == 0) {
                    // Actually start updating once all components are loaded.
                    setTimeout(function() { me.update(); }, me.options.updaterate);
                }
            });
        });
        if (me.nogui) {
            setTimeout(function() { me.update(); }, me.options.updaterate);
        }
        else {
            setTimeout(function() {
                $.each(configset, function(type, configlist) {
                    if(tobeloaded[type]) {
                        me.alertbox
                            .text('Error loading js/' + type + '.js')
                            .show();
                    }
                });
                if(componentstoload > 0)
                    setTimeout(function() { me.update(); }, me.options.updaterate);
            }, 15000);
        }
    },
    getProjection: function() { return this.maptype.getProjection(); },
    selectMapAndPan: function(map, location, completed) {
        if (!map) { throw "Cannot select map " + map; }
        let me = this;

        if (me.maptype === map) {
            return;
        }
        $(me).trigger('mapchanging');
        let mapWorld = map.options.world;
        if (me.maptype) {
            $('.compass').removeClass('compass_' + me.maptype.options.compassview);
            $('.compass').removeClass('compass_' + me.maptype.options.name);
        }
        $('.compass').addClass('compass_' + map.options.compassview);
        $('.compass').addClass('compass_' + map.options.name);
        let worldChanged = me.world !== map.options.world;
        let projectionChanged = (me.maptype && me.maptype.getProjection()) !== (map && map.projection);

        let prevzoom = me.map.getZoom();

        let prevworld = me.world;

        if(worldChanged) {	// World changed - purge URL cache (tile updates unreported for other worlds)
            me.registeredTiles = [];
            me.inittime = new Date().getTime();
        }

        if(worldChanged && me.world) {
            me.world.lastcenter = me.maptype.getProjection().fromLatLngToLocation(me.map.getCenter(), 64);
        }

        if (me.maptype) {
            me.map.removeLayer(me.maptype);
        }

        let prevmap = me.maptype;

        me.world = mapWorld;
        me.maptype = map;

        if(me.maptype.options.maxZoom < prevzoom)
            prevzoom = me.maptype.options.maxZoom;
        me.map.options.maxZoom = me.maptype.options.maxZoom;
        me.map.options.minZoom = me.maptype.options.minZoom;

        if (projectionChanged || worldChanged || location) {
            let centerPoint;
            if(location) {
                centerPoint = me.getProjection().fromLocationToLatLng(location);
            }
            else if(worldChanged) {
                let centerLocation;
                if(mapWorld.lastcenter)
                    centerLocation = mapWorld.lastcenter;
                else
                    centerLocation = $.extend({ x: 0, y: 64, z: 0 }, mapWorld.center);
                centerPoint = me.getProjection().fromLocationToLatLng(centerLocation);
            }
            else {
                let prevloc = null;
                if(prevmap != null)
                    prevloc = prevmap.getProjection().fromLatLngToLocation(me.map.getCenter(), 64);
                if(prevloc != null)
                    centerPoint = me.getProjection().fromLocationToLatLng(prevloc);
                else
                    centerPoint = me.map.getCenter();
            }
            me.map.setView(centerPoint, prevzoom, true);
        }
        else {
            me.map.setZoom(prevzoom);
        }
        me.map.addLayer(me.maptype);

        if (worldChanged) {
            $(me).trigger('worldchanged');
        }
        $(me).trigger('mapchanged');

        $('.map', me.worldlist).removeClass('selected');
        $(map.element).addClass('selected');
        me.updateBackground();


        if (completed) {
            completed();
        }
    },
    selectMap: function(map, completed) {
        this.selectMapAndPan(map, null, completed);
    },
    selectWorldAndPan: function(world, location, completed) {
        let me = this;
        if (typeof(world) === 'String') { world = me.worlds[world]; }
        if (me.world === world) {
            if(location) {
                let latlng = me.maptype.getProjection().fromLocationToLatLng(location);
                me.panToLatLng(latlng, completed);
            }
            else {
                if (completed) { completed(); }
            }
            return;
        }
        me.selectMapAndPan(world.defaultmap, location, completed);
    },
    selectWorld: function(world, completed) {
        this.selectWorldAndPan(world, null, completed);
    },
    panToLocation: function(location, completed) {
        let me = this;

        if (location.world) {
            me.selectWorldAndPan(location.world, location, function() {
                if(completed) completed();
            });
        } else {
            let latlng = me.maptype.getProjection().fromLocationToLatLng(location);
            me.panToLatLng(latlng, completed);
        }
    },
    panToLayerPoint: function(point, completed) {
        let me = this;
        let latlng = me.map.layerPointToLatLng(point);
        me.map.panToLatLng(latlng);
        if (completed) {
            completed();
        }
    },
    panToLatLng: function(latlng, completed) {
        this.map.panTo(latlng);
        if (completed) {
            completed();
        }
    },
    update: function() {
        let me = this;

        $(me).trigger('worldupdating');
        $.getJSON(me.formatUrl('update', { world: me.world.name, timestamp: me.lasttimestamp, reqid: me.reqid }), function(update) {
                me.reqid++; // Bump request ID always
                if (!update) {
                    setTimeout(function() { me.update(); }, me.options.updaterate);
                    return;
                }
                me.alertbox.hide();

                if(update.error) {
                    if(update.error == 'login-required') {
                        me.saveURL();
                        window.location = 'login.html';
                    }
                    else {
                        alert(update.error);
                    }
                    return;
                }
                if (me.lasttimestamp == update.timestamp) { // Same as last update?
                    setTimeout(function() { me.update(); }, me.options.updaterate);
                    return;
                }

                if (!me.options.jsonfile) {
                    me.lasttimestamp = update.timestamp;
                }
                if(me.options.confighash != update.confighash) {
                    window.location.reload(true);
                    return;
                }
                me.playerfield.text(me.options['msg-players'] + " [" + update.currentcount + "/" + me.options.maxcount + "]");

                me.servertime = update.servertime;
                let newserverday = (me.servertime > 23100 || me.servertime < 12900);
                if(me.serverday != newserverday) {
                    me.serverday = newserverday;

                    me.updateBackground();
                    if(me.maptype.options.nightandday) {
                        // Readd map.
                        me.map.removeLayer(me.maptype);
                        me.map.addLayer(me.maptype);
                    }
                }

                let newplayers = {};
                $.each(update.players, function(index, playerUpdate) {
                    let acct = playerUpdate.account;
                    let player = me.players[acct];
                    if (player) {
                        me.updatePlayer(player, playerUpdate);
                    } else {
                        me.addPlayer(playerUpdate);
                        if(me.initfollow && (me.initfollow == acct)) {
                            me.followPlayer(me.players[acct]);
                            me.initfollow = null;
                        }
                    }
                    newplayers[acct] = player;
                });
                let acct;
                for(acct in me.players) {
                    let player = me.players[acct];
                    if(!(acct in newplayers)) {
                        me.removePlayer(player);
                    }
                }

                $.each(update.updates, function(index, update) {
                    // Only handle updates that are actually new.
                    if(!me.options.jsonfile || me.lasttimestamp <= update.timestamp) {
                        $(me).trigger('worldupdate', [ update ]);

                        swtch(update.type, {
                            tile: function() {
                                me.onTileUpdated(update.name,update.timestamp);
                            },
                            playerjoin: function() {
                                $(me).trigger('playerjoin', [ update.playerName ]);
                            },
                            playerquit: function() {
                                $(me).trigger('playerquit', [ update.playerName ]);
                            },
                            component: function() {
                                $(me).trigger('component.' + update.ctype, [ update ]);
                            }
                        });
                    }
                    /* remove older messages from chat*/
                    //let timestamp = event.timeStamp;
                    //let divs = $('div[rel]');
                    //divs.filter(function(i){return parseInt(divs[i].attr('rel')) > timestamp+me.options.messagettl;}).remove();
                });

                $(me).trigger('worldupdated', [ update ]);

                me.lasttimestamp = update.timestamp;
                me.missedupdates = 0;
                setTimeout(function() { me.update(); }, me.options.updaterate);
            }, function(status, statusText, request) {
                me.lasttimestamp--;	// Avoid same TS URL
                me.missedupdates++;
                if(me.missedupdates > 2) {
                    me.alertbox
                        .text('Could not update map: ' + (statusText || 'Could not connect to server'))
                        .show();
                    $(me).trigger('worldupdatefailed');
                }
                setTimeout(function() { me.update(); }, me.options.updaterate);
            }
        );
    },
    getTileUrl: function(tileName, always) {
        let me = this;
        let tile = me.registeredTiles[tileName];

        if(tile == null) {
            let url = me.options.url.tiles;
            if(url.indexOf('?') > 0)
                tile = this.registeredTiles[tileName] = url + escape(me.world.name + '/' + tileName) + '&ts=' + me.inittime;
            else
                tile = this.registeredTiles[tileName] = url + me.world.name + '/' + tileName + '?' + me.inittime;
        }
        return tile;
    },
    onTileUpdated: function(tileName,timestamp) {
        let me = this;
        let url = me.options.url.tiles;
        if(url.indexOf('?') > 0)
            this.registeredTiles[tileName] = url + escape(me.world.name + '/' + tileName) + '&ts=' + timestamp;
        else
            this.registeredTiles[tileName] = url + me.world.name + '/' + tileName + '?' + timestamp;
        me.maptype.updateNamedTile(tileName);
    },
    addPlayer: function(update) {
        let me = this;
        let player = me.players[update.account] = {
            name: update.name,
            location: new Location(me.worlds[update.world], parseFloat(update.x), parseFloat(update.y), parseFloat(update.z)),
            health: update.health,
            armor: update.armor,
            account: update.account,
            sort: update.sort
        };

        $(me).trigger('playeradded', [ player ]);

        // Create the player-menu-item.
        let playerIconContainer;
        let menuitem = player.menuitem = $('<li/>')
            .addClass('player')
            .append(playerIconContainer = $('<span/>')
                .addClass('playerIcon')
                .append($('<img/>').attr({ src: 'images/player_face.png' }))
                .attr({ title: 'Follow player' })
                .click(function() {
                    let follow = player !== me.followingPlayer;
                    me.followPlayer(follow ? player : null);
                })
            )
            .append(player.menuname = $('<a/>')
                .attr({
                    href: '#',
                    title: 'Center on player'
                })
                .append(player.name)
            )
            .click(function(e) {
                if (me.followingPlayer !== player) {
                    me.followPlayer(null);
                }
                if(player.location.world)
                    me.panToLocation(player.location);
            });
        player.menuname.data('sort', player.sort);
        // Inject into playerlist alphabetically
        let firstNodeAfter = me.playerlist.children().filter(function() {
            let itm = $('a', this);
            let sort = itm.data('sort');
            if (sort > player.sort) return true;
            if (sort < player.sort) return false;
            return (itm.text().toLowerCase() > player.menuname.text().toLowerCase());
        }).eq(0);
        if (firstNodeAfter.length > 0) {
            firstNodeAfter.before(menuitem);
        } else {
            menuitem.appendTo(me.playerlist);
        }
        if (me.options.showplayerfacesinmenu) {
            getMinecraftHead(player.account, 16, function(head) {
                $('img', playerIconContainer).remove();
                $(head).appendTo(playerIconContainer);
            });
        }
    },
    updatePlayer: function(player, update) {
        let me = this;
        let location = player.location = new Location(me.worlds[update.world], parseFloat(update.x), parseFloat(update.y), parseFloat(update.z));
        player.health = update.health;
        player.armor = update.armor;
        player.name = update.name;

        $(me).trigger('playerupdated', [ player ]);

        if (player.menuname && (player.menuname.html() != player.name)) {
            player.menuname.html(player.name);
        }

        // Update menuitem.
        if(me.options.grayplayerswhenhidden)
            player.menuitem.toggleClass('otherworld', me.world !== location.world);

        if (player === me.followingPlayer) {
            // Follow the updated player.
            me.panToLocation(player.location);
        }
    },
    removePlayer: function(player) {
        let me = this;

        delete me.players[player.account];

        $(me).trigger('playerremoved', [ player ]);

        // Remove menu item.
        player.menuitem.remove();
    },
    followPlayer: function(player) {
        let me = this;
        $('.following', me.playerlist).removeClass('following');

        if(player) {
            if(!player.location.world)
                return;
            $(player.menuitem).addClass('following');
            me.panToLocation(player.location, function() {
                if(me.options.followmap && me.world) {
                    let pmap = me.world.maps[me.options.followmap];
                    if(pmap)
                        me.selectMapAndPan(pmap);
                }
                if(me.options.followzoom)
                    me.map.setZoom(me.options.followzoom);
            });
        }
        this.followingPlayer = player;
    },
    updateBackground: function() {
        let me = this;
        let col = "#000000";
        if(me.serverday) {
            if(me.maptype.options.backgroundday)
                col = me.maptype.options.backgroundday;
            else if(me.maptype.options.background)
                col = me.maptype.options.background;
        }
        else {
            if(me.maptype.options.backgroundnight)
                col = me.maptype.options.backgroundnight;
            else if(me.maptype.options.background)
                col = me.maptype.options.background;
        }
        $('.map').css('background', col);
        $('.leaflet-tile').css('background', col);
    },
    getParameterByName: function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        let regexS = "[\\?&]"+name+"=([^&#]*)";
        let regex = new RegExp( regexS );
        let results = regex.exec( window.location.href );
        if( results == null )
            return "";
        else
            return decodeURIComponent(results[1].replace(/\+/g, " "));
    },
    getIntParameterByName: function(name) {
        let v = this.getParameterByName(name);
        if(v != "") {
            v = parseInt(v, 10);
            if(!isNaN(v)) {
                return v;
            }
        }
        return null;
    },
    getBoolParameterByName: function(name) {
        let v = this.getParameterByName(name);
        if(v != "") {
            if(v == "true")
                return true;
            else if(v == "false")
                return false;
        }
        return null;
    },

    layersetlist: [],

    addToLayerSelector: function(layer, name, priority) {
        let me = this;

        if(me.options.showlayercontrol != "false" && (!me.layercontrol)) {
            me.layercontrol = new DynmapLayerControl();
            if(me.options.showlayercontrol == "pinned")
                me.layercontrol.options.collapsed = false;
            map.addControl(me.layercontrol);
        }

        let i;
        for(i = 0; i < me.layersetlist.length; i++) {
            if(me.layersetlist[i].layer === layer) {
                me.layersetlist[i].priority = priority;
                me.layersetlist[i].name = name;
                break;
            }
        }
        if(i >= me.layersetlist.length) {
            me.layersetlist[i] = { layer: layer, priority: priority, name: name };
        }
        me.layersetlist.sort(function(a, b) {
            if(a.priority != b.priority)
                return a.priority - b.priority;
            else
                return ((a.name < b.name) ? -1 : ((a.name > b.name) ? 1 : 0));
        });
        if(me.options.showlayercontrol != "false") {
            for(i = 0; i < me.layersetlist.length; i++) {
                me.layercontrol.removeLayer(me.layersetlist[i].layer);
            }
            for(i = 0; i < me.layersetlist.length; i++) {
                me.layercontrol.addOverlay(me.layersetlist[i].layer, me.layersetlist[i].name);
            }
        }
    },
    removeFromLayerSelector: function(layer) {
        let me = this;
        let i;
        for(i = 0; i < me.layersetlist.length; i++) {
            if(me.layersetlist[i].layer === layer) {
                me.layersetlist.splice(i, 1);
                if(me.options.showlayercontrol != "false")
                    me.layercontrol.removeLayer(layer);
                break;
            }
        }
    },
    getLink: function() {
        let me = this;
        let url = window.location.pathname;
        let center = me.maptype.getProjection().fromLatLngToLocation(me.map.getCenter(), 64);
        url = url + "?worldname=" + me.world.name + "&mapname=" + me.maptype.options.name + "&zoom=" + me.map.getZoom() + "&x=" + center.x + "&y=" +
            center.y + "&z=" + center.z;
        return url;
    },
    initLogin: function() {
        let me = this;
        if(!me.options['login-enabled'])
            return;

        let login = L.Control.extend({
            onAdd: function(map) {
                this._container = L.DomUtil.create('div', 'logincontainer');
                this._map = map;
                this._update();
                return this._container;
            },
            getPosition: function() {
                return 'bottomright';
            },
            getContainer: function() {
                return this._container;
            },
            _update: function() {
                if (!this._map) return;
                let c = this._container;
                let cls = 'loginbutton';
                if(me.options.sidebaropened != 'false') {
                    cls = 'loginbutton pinnedloginbutton';
                }
                if (me.options.loggedin) {
                    c = $('<button/>').addClass(cls).click(function(event) {
                        $.ajax({
                            type: 'POST',
                            contentType: "application/json; charset=utf-8",
                            url: config.url.login,
                            success: function(response) {
                                window.location = "index.html";
                            }
                        });
                    }).text('Logout').appendTo(c)[0];
                }
                else {
                    c = $('<button/>').addClass(cls).click(function(event) {
                        me.saveURL();
                        window.location = "login.html";
                    }).text('Login').appendTo(c)[0];
                }
            }
        });
        let l = new login();
        me.map.addControl(l);
    },
    saveURL : function() {
        if(window.location.href.indexOf('?') > 0) {
            document.cookie="dynmapurl=" + escape(window.location);
        }
    },
    checkForSavedURL : function() {
        let i,x,y,ourcookies=document.cookie.split(";");
        for (i=0;i<ourcookies.length;i++) {
            x=ourcookies[i].substr(0,ourcookies[i].indexOf("="));
            y=ourcookies[i].substr(ourcookies[i].indexOf("=")+1);
            x=x.replace(/^\s+|\s+$/g,"");
            if (x == "dynmapurl") {
                let v = unescape(y);
                document.cookie='dynmapurl=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
                if((v.indexOf('?') >= 0) && (v != window.location)) {
                    window.location = v;
                    return true;
                }
            }
        }
        return false;
    }
};


let HDProjection = DynmapProjection.extend({
    fromLocationToLatLng: function(location) {
        let wtp = this.options.worldtomap;
        let xx = wtp[0]*location.x + wtp[1]*location.y + wtp[2]*location.z;
        let yy = wtp[3]*location.x + wtp[4]*location.y + wtp[5]*location.z;
        return new L.LatLng(
            xx       / (1 << this.options.mapzoomout)
            , (128-yy) / (1 << this.options.mapzoomout)
            , true);
    },
    fromLatLngToLocation: function(latlon, y) {
        let ptw = this.options.maptoworld;
        let lat = latlon.lat * (1 << this.options.mapzoomout);
        let lon = 128 - latlon.lng * (1 << this.options.mapzoomout);
        let x = ptw[0]*lat + ptw[1]*lon + ptw[2]*y;
        let z = ptw[6]*lat + ptw[7]*lon + ptw[8]*y;
        return { x: x, y: y, z: z };
    }

});

let HDMapType = DynmapTileLayer.extend({
    projection: undefined,
    options: {
        minZoom: 0,
        maxZoom: 0,
        errorTileUrl: 'images/blank.png',
        continuousWorld: true
    },
    initialize: function(options) {
        options.maxZoom = options.mapzoomin + options.mapzoomout;
        L.Util.setOptions(this, options);
        this.projection = new HDProjection($.extend({map: this}, options));
    },
    getTileName: function(tilePoint, zoom) {
        let info = this.getTileInfo(tilePoint, zoom);
        // Y is inverted for HD-map.
        info.y = -info.y;
        info.scaledy = info.y >> 5;
        return namedReplace('{prefix}{nightday}/{scaledx}_{scaledy}/{zoom}{x}_{y}.{fmt}', info);
    },
    zoomprefix: function(amount) {
        // amount == 0 -> ''
        // amount == 1 -> 'z_'
        // amount == 2 -> 'zz_'
        return 'zzzzzzzzzzzzzzzzzzzzzz'.substr(0, amount) + (amount === 0 ? '' : '_');
    }
});

maptypes.HDMapType = function(options) { return new HDMapType(options); };

let KzedProjection = DynmapProjection.extend({
    fromLocationToLatLng: function(location) {
        let dx = location.x;
        let dy = location.y - 127;
        let dz = location.z;
        let px = dx + dz;
        let py = dx - dz - dy;
        let scale = 1 << this.options.mapzoomout;

        let xx = (128 - px) / scale;
        let yy = py / scale;
        return new L.LatLng(xx, yy, true);
    },
    fromLatLngToLocation: function(latlon, y) {
        let scale = 1 << this.options.mapzoomout;
        let px = 128 - (latlon.lat * scale);
        let py = latlon.lng * scale;
        let x = (px + py + (y-127))/2;
        let z = (px - x);
        return { x: x, y: y, z: z };
    }

});

let KzedMapType = DynmapTileLayer.extend({
    options: {
        minZoom: 0,
        maxZoom: 4,
        errorTileUrl: 'images/blank.png',
        continuousWorld: true
    },
    initialize: function(options) {
        options.maxZoom = options.mapzoomin + options.mapzoomout;
        L.Util.setOptions(this, options);
        this.projection = new KzedProjection({mapzoomout: this.options.mapzoomout});
    },
    getTileName: function(tilePoint, zoom) {
        let info = this.getTileInfo(tilePoint, zoom);
        return namedReplace(this.options.bigmap
            ? '{zprefix}{nightday}/{scaledx}_{scaledy}/{zoomprefix}{x}_{y}.png'
            : '{zoom}{prefix}{nightday}_{x}_{y}.png'
            , this.getTileInfo(tilePoint, zoom));
    },
    getTileInfo: function(tilePoint, zoom) {
        // Custom tile-info-calculation for KzedMap: *128 and >>12
        let izoom = this.options.maxZoom - zoom;
        let zoomoutlevel = Math.max(0, izoom - this.options.mapzoomin);
        let scale = 1 << zoomoutlevel;
        let x = -scale*tilePoint.x*128;
        let y = scale*tilePoint.y*128;
        return {
            prefix: this.options.prefix,
            nightday: (this.options.nightandday && this.options.dynmap.serverday) ? '_day' : '',
            scaledx: x >> 12,
            scaledy: y >> 12,
            zoom: this.zoomprefix(zoomoutlevel),
            zoomprefix: (zoomoutlevel<2)?"":(this.zoomprefix(zoomoutlevel-1)+"_"),
            zprefix: (zoomoutlevel==0)?this.options.prefix:("z"+this.options.prefix),
            x: x,
            y: y
        };
    }
});

maptypes.KzedMapType = function(configuration) { return new KzedMapType(configuration); };

let FlatProjection = DynmapProjection.extend({
    fromLocationToLatLng: function(location) {
        return new L.LatLng(
            -location.z / (1 << this.options.mapzoomout),
            location.x / (1 << this.options.mapzoomout),
            true);
    },
    fromLatLngToLocation: function(latlon, y) {
        let z = -latlon.lat * (1 << this.options.mapzoomout);
        let x = latlon.lng * (1 << this.options.mapzoomout);
        return { x: x, y: y, z: z };
    }

});

let FlatMapType = DynmapTileLayer.extend({
    options: {
        minZoom: 0,
        maxZoom: 4,
        errorTileUrl: 'images/blank.png',
        continuousWorld: true
    },
    initialize: function(options) {
        options.maxZoom = options.mapzoomin + options.mapzoomout;
        L.Util.setOptions(this, options);
        this.projection = new FlatProjection({mapzoomout: options.mapzoomout});
    },
    getTileName: function(tilePoint, zoom) {
        return namedReplace(this.options.bigmap
            ? '{prefix}{nightday}_128/{scaledx}_{scaledy}/{zoomprefix}{x}_{y}.png'
            : '{zoom}{prefix}{nightday}_128_{x}_{y}.png'
            , this.getTileInfo(tilePoint, zoom));
    }
});

maptypes.FlatMapType = function(options) { return new FlatMapType(options); };

let config = {
    url : {
        configuration: 'up/configuration',
        update: 'up/world/{world}/{timestamp}',
        sendmessage: 'up/sendmessage',
        login: 'up/login',
        register: 'up/register',
        tiles: 'tiles/',
        markers: 'tiles/'
    }
};


$(document).ready(function() {
    window.dynmap = new DynMap($.extend({
        container: $('#mcmap')
    }, config));
});