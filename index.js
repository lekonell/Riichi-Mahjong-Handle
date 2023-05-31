const express = require('express');
const app = express();
const SERVER_PORT = 80;

app.listen(SERVER_PORT, () => {
    console.log(`Server Listening at port ${SERVER_PORT}`);
})

app.use(express.static(__dirname));

app.get('/', (req, res) => {
    //
})