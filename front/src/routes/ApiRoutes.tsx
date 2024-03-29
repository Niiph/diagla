const links = {
    login: () => '/login_check',
    register: () => '/register',
    tokenRefresh: () => '/token_refresh',
    tokenInvalidate: () => '/token_invalidate',

    deviceChangeActive: (id?: string) => links.devices(id) + '/change_active',
    deviceChangePassword: (id?: string) => links.devices(id) + '/change_password',
    deviceChangeName: (id?: string) => links.devices(id) + '/change_name',
    devices: (id?: string) => '/devices' + (id ? '/' + id : ''),
    devicesFullList: () => '/devices/full_list',
    devicesSimpleList: () => '/devices/simple_list',

    sensors: (id?: string) => '/sensors' + (id ? '/' + id : ''),
    sensorDetails: (id?: string) => links.sensors(id) + '/details',
    sensorChangeActive: (id?: string) => links.sensors(id) + '/change_active',
    sensorChangeName: (id?: string) => links.sensors(id) + '/change_name',
    sensorChangeMinimum: (id?: string) => links.sensors(id) + '/change_minimum',
    sensorChangeMaximum: (id?: string) => links.sensors(id) + '/change_maximum',
    sensorChangeAddress: (id?: string) => links.sensors(id) + '/change_address',
    sensorChangeDevice: (id?: string) => links.sensors(id) + '/change_device',
    sensorChangePin: (id?: string) => links.sensors(id) + '/change_pin',
    sensorReadings: (id?: string) => links.sensors(id) + '/readings'
};

const aliases = (key: keyof typeof links, id?: string): string => {
    const apiUrl = `${window.location.protocol}//${window.location.hostname}${process.env.REACT_APP_API_URL}`;
    const link = links[key](id);

    if (apiUrl && link) {
        return `${apiUrl}${link}`;
    } else {
        throw new Error(`Invalid key: ${key}`);
    }
};

export default aliases;
