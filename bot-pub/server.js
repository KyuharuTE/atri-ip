import express from 'express';
import { Bot } from './bot.js';
import config from './config.js';

const bot = new Bot();
const app = express();
const port = config.API_SERVER_PORT;

app.use(express.json());

const sendErrorResponse = (res, status, message, error = null) => {
    const response = {
        success: false,
        message
    };
    if (error && process.env.NODE_ENV !== 'production') {
        response.error = error.message;
    }
    res.status(status).json(response);
};

app.post('/api/getresid', async (req, res) => {
    const receivedData = req.body;

    let body;
    try {
        body = JSON.stringify(receivedData);
    } catch (error) {
        console.error('JSON stringify error:', error);
        return sendErrorResponse(res, 400, 'Invalid JSON data', error);
    }

    try {
        const resid = await bot.botGetResId(body);
        res.status(200).json({
            success: true,
            message: resid
        });
    } catch (error) {
        console.error('GetResId error:', error);
        return sendErrorResponse(res, 500, 'Failed to get resid', error);
    }
});

app.post('/api/getark', async (req, res) => {
    const { title, desc, url } = req.body;

    if (!title || !desc || !url) {
        return sendErrorResponse(res, 400, 'Missing required fields: title, desc, or url');
    }

    try {
        const ark = await bot.botGetArk(title, desc, url);
        res.status(200).json({
            success: true,
            message: ark
        });
    } catch (error) {
        console.error('GetArk error:', error);
        return sendErrorResponse(res, 500, 'Failed to get ark', error);
    }
});

app.use((err, req, res, next) => {
    console.error('Unhandled error:', err);
    sendErrorResponse(res, 500, 'Internal server error', err);
});

app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});