var
    env = require('dotenv').config({path: '../.env'}),
    redis_host = process.env.REDIS_HOST ? process.env.REDIS_HOST : '127.0.0.1',
    redis_port = process.env.REDIS_PORT ? process.env.REDIS_PORT : 6379,
    socket_port = process.env.SOCKET_PORT ? process.env.SOCKET_PORT : 9001;

var app = require('express')(),
    http = require('http'),
    server = http.createServer(app),
    io = require('socket.io')(server),
    redis = require('ioredis')(redis_port, redis_host);

server.listen(socket_port);

io.sockets.on('connection', function(socket) {
    //console.log('connection', socket.id);
    socket.on('join', function(room) {
        //console.log(room);
        socket.join(room);
    });
});

redis.psubscribe('*', function(err, count) { });

redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.sockets.in(channel).emit(message.event, message.data);
});
