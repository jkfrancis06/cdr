window.onload = function () {
    Vue.component('v-pagination', window['vue-plain-pagination'])

    var app = new Vue({
        el: '#app',
        delimiters: ['${', '}'],
        data: {
            edges : [],
            nodes: []
        },

        watch: {
        },

        mounted (){
            tp = this
            href = '/communication-graph/'
            axios.get(href)
                .then(function (response) {
                    console.log(response)
                    for (let i = 0 ; i < response.data.nodes.length; i++){
                        var temp_node = {
                            id : response.data.nodes[i],
                            label: response.data.nodes[i],
                            x: Math.random(),
                            y: Math.random(),
                            size: 1,
                            color: "#"+ Math.floor(Math.random()*16777215).toString(16)
                        }
                        tp.nodes.push(temp_node)
                    }

                    for (let i = 0 ; i < response.data.edges.length; i++){
                        var temp_edge = {
                            id : response.data.edges[i].source +'-'+ response.data.edges[i].dest,
                            source: response.data.edges[i].source,
                            target: response.data.edges[i].dest,
                            color: '#202020',
                            type: 'curvedArrow',
                        }
                        tp.edges.push(temp_edge)
                    }
                    tp.draw_sigma();
                    console.log(tp.nodes)
                })

        },

        beforeDestroy() {
            // remove pickadate according to its API
        },

        methods : {
            draw_sigma: function () {
                self = this
                console.log(this.nodes)
                console.log(this.edges)
                var myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                    keyboard: false
                })

                // Initialize sigma:
                var s = new sigma(
                    {
                        renderer: {
                            container: document.getElementById('sigma-container'),
                            type: 'canvas'
                        },
                        settings: {
                            minArrowSize: 10
                        }
                    }
                );

                var graph = {
                    nodes: self.nodes,
                    edges: self.edges
                }
                s.graph.read(graph);
// call the plugin
                sigma.plugins.relativeSize(s, 1);
// draw the graph
                s.refresh();
// launch force-atlas for 5sec
                s.startForceAtlas2();
                window.setTimeout(function() {s.killForceAtlas2()}, 10000);
            }
        }
    })
}
