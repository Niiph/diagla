import * as icons from '@ant-design/icons';
import axios from 'axios';
import Links from 'routes/ApiRoutes';

const fetchData = async () => {
    const config = {
        headers: {
            Authorization: 'Bearer ' + localStorage.getItem('jwt_token')
        }
    };

    try {
        const response = await axios.get(Links('devices'), config);
        localStorage.setItem('devices', JSON.stringify(response.data));
        return response.data;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
};

const initializeDashboard = async () => {
    try {
        fetchData();
        const data = JSON.parse(localStorage.getItem('devices'));
        console.log(data);
        const devices = data['hydra:member'];

        const updatedChildren = devices.map((device) => {
            const deviceId = device.id;
            const deviceTitle = device.name;

            const sensorItems = device.sensors.map((sensor) => {
                const sensorId = sensor.id;
                const sensorTitle = sensor.name;

                return {
                    id: sensorId,
                    title: sensorTitle,
                    type: 'item',
                    url: `/sensor/${sensorId}`,
                    icon: icons.AimOutlined,
                    breadcrumbs: false
                };
            });

            return {
                id: deviceId,
                title: deviceTitle,
                type: 'collapse',
                url: `/device/${deviceId}`,
                icon: icons.DashboardOutlined,
                breadcrumbs: false,
                children: sensorItems
            };
        });

        return updatedChildren;
    } catch (error) {
        console.error('Error fetching data:', error);
        throw error;
    }
};

const InitializeDashboardAsync = async () => {
    const children = await initializeDashboard();
    Devices.children = [
        {
            id: 'devices-group',
            title: 'Devices',
            type: 'collapse',
            url: `/devices`,
            icon: icons.LoginOutlined,
            breadcrumbs: false,
            children: children
        }
    ];
};

const Devices = {
    id: 'group-devices',
    title: 'Devices',
    type: 'group',
    breadcrumbs: false,
    children: [
        {
            id: 'devices-group',
            title: 'Devices',
            type: 'collapse',
            url: `/devices`,
            icon: icons.LoginOutlined,
            breadcrumbs: false,
            children: []
        }
    ]
};

// Call the initializeDashboardAsync function to populate the devices with data
InitializeDashboardAsync();

export default Devices;