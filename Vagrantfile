Vagrant.configure("2") do |config|
    config.vm.provider "virtualbox" do |v|
      v.memory = 2048
      v.cpus = 2
    end
    config.vm.box = "scotch/box"
    config.vm.network "private_network", ip: "192.168.33.10"
    config.vm.hostname = "form.box"
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = true
    config.vm.synced_folder ".", "/var/www", :mount_options => ["dmode=777", "fmode=666"]
end
